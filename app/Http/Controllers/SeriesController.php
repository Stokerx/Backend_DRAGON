<?php

namespace App\Http\Controllers;

use App\Models\FunctionSerie;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ActivitySeriesUserController;
use mysql_xdevapi\Statement;

class SeriesController extends Controller
{
    public function createSerie(Request $request, ActivitySeriesUserController $activityController)
    {
        try {
            //validacion de los datos de entrada
            $request->validate([
                'img_url' => 'required|image|mimes:jpeg,png,jpg,svg,webp|max:3072',
                'name' => 'required|unique:series', // Agregamos la regla unique aquí
                'day_issue' => 'required',
                'status' => 'required',
                'classification' => 'required',
                'functions' => 'required|array', // 'functions' => 'required|array
                'functions.*.type_id' => 'required|in: 1,2,3,4', //function series
                'functions.*.value' => 'required',//validation for FunctionSerie
            ]);

            $series = Series::create(array_merge($request->all(['img_url', 'name', 'day_issue', 'status', 'classification'])));
            $activityController->insertActivitySeriesUserspecify($request->user()->id, $series->id);
            if ($request->hasFile('img_url')) {
                $series->img_url = $this->storeSeriesImage($request);
                $series->save();
            }

            // Store function series
            $this->storeFunctionSeries($series, $request->functions);

            return response()->json(['message' => 'La serie a sido creada',
                'serie' => $series], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear la serie', 'error' => $e->getMessage()], 500);
        }
    }

    private function storeSeriesImage(Request $request)
    {
        $file_name = $request->file('img_url')->store('series_image', 'public');
        return $file_name;
    }

    private function storeFunctionSeries(Series $series, array $functions)
    {
        foreach ($functions as $function) {
            FunctionSerie::create([
                'series_id' => $series->id,
                'type_id' => $function['type_id'],
                'value' => $function['value'],
            ]);
        }
    }

    public function getAllSeries()
    {
        try {
            $user = auth()->user();

            // Clave de caché basada en el ID del usuario
            $cacheKey = 'series_' . $user->id;

            $series = Cache::remember($cacheKey, 3, function () use ($user) {
                $query = Series::with(['functionSeries' => function ($query) {
                    $query->join('type_function', 'type_function.id', '=', 'function_series.type_id')
                        ->select('function_series.series_id', 'function_series.type_id', 'function_series.value', 'type_function.name');
                }])
                    ->select('id', 'name', DB::raw("CONCAT('".env('APP_URL').'/storage/'."', img_url) AS img_url"), 'status', 'classification', 'day_issue');

                if ($user->hasRole('admin') || $user->hasRole('dragon')) {
                    // Los usuarios con roles 'admin' o 'dragon' pueden ver todas las series
                    return $query->get();
                } else {
                    // Para los demás usuarios, filtrar las series asignadas o con clasificación 'libre'
                    return $query->where(function ($query) use ($user) {
                        $query->whereHas('users', function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        })
                            ->orWhere('classification', 'libre');
                    })->get();
                }
            });

            return response()->json($series, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function getFunctionSeries(Series $series)
    {
        try {
            $functionSeries = $series->functionSeries()
                ->join('type_function', 'type_function.id', '=', 'function_series.type_id')
                ->select('function_series.id', 'function_series.type_id', 'function_series.value', 'type_function.name')
                ->get();
            return response()->json($functionSeries);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener las function series', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, Series $series)
    {
        try {
            // Validate the request data
            $request->validate([
                'status' => 'required',
            ]);

            // Update the status of the series
            $series->update(['status' => $request->status]);

            return response()->json(['message' => 'Status updated successfully', 'series' => $series], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating status', 'error' => $e->getMessage()], 500);
        }

    }

    public function updateFunctionsSerie(Request $request, $serieId)
    {
        try{
            $request->validate([
                'functions' => 'required|array',
                'functions.*.type_id' => 'required|in:1,2,3,4',
                'functions.*.value' => 'required',
            ]);

            // Find the series by its ID
            $series = Series::find($serieId);

            if (!$series) {
                return response()->json(['message' => 'Series not found'], 404);
            }

            // Update the function series
            foreach($request->functions as $function){
                FunctionSerie::where('series_id', $series->id)
                    ->where('type_id', $function['type_id'])
                    ->update(['value' => $function['value']]);
            }

            return response()->json(['message' => 'Function series updated successfully', 'serie' => $series], 200);
        }catch (\Exception $e){
            return response()->json(['message' => 'Error updating function series', 'error' => $e->getMessage()], 500);
        }
    }


    public function updateSerie(Request $request, $serieId, ActivitySeriesUserController $activityController)
    {
        try {
            $request->validate([
                'img_url' => 'image|mimes:jpeg,png,jpg,svg,webp|max:3072',
                'name' => 'string',
                'day_issue' => 'string',
                'status' => 'string',
                'classification' => 'string',
                'functions' => 'array',
                'functions.*.type_id' => 'in:1,2,3,4',
                'functions.*.value' => 'numeric',
            ]);

            // Find the series by its ID
            $series = Series::find($serieId);

            if (!$series) {
                return response()->json(['message' => 'Series not found'], 404);
            }

            $series->update($request->only(['name', 'day_issue', 'status', 'classification']));

            if ($request->hasFile('img_url')) {
                // Delete the old series image
                if($series->img_url) {
                    Storage::disk('public')->delete($series->img_url);
                }

                $series->img_url = $this->storeSeriesImage($request);
                $series->save();
            }

            $functionUpdated = false; // Flag to check if any function value was updated

            if ($request->has('functions')) {
                foreach($request->functions as $function){
                    $updateCount = FunctionSerie::where('series_id', $series->id)
                        ->where('type_id', $function['type_id'])
                        ->update(['value' => $function['value']]);

                    if ($updateCount > 0) {
                        $functionUpdated = true; // Set flag to true if any function was updated
                    }
                }
            }

            // Call insertActivityUpdateValueSeries if any function value was updated
            if ($functionUpdated) {
                $activityController->insertActivityUpdateValueSeries($request->user()->id, $series->id);
            }

            return response()->json(['message' => 'Serie and function series updated successfully', 'serie' => $series], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating serie and function series', 'error' => $e->getMessage()], 500);
        }
    }




    public function deleteSerie($series)
    {
        $series = Series::find($series);

        if (!$series) {
            return response()->json(['message' => 'Serie not found'], 404);
        }

        DB::beginTransaction();
        try {
            // Delete the image from storage if it exists
            if ($series->img_url && Storage::disk('public')->exists($series->img_url)) {
                Storage::disk('public')->delete($series->img_url);
            }

            DB::statement('delete from activity_series_users where series_id = ?', [$series->id]);
            DB::statement('delete from chapters where series_id = ?', [$series->id]);
            DB::statement('delete from function_series where series_id = ?', [$series->id]);
            DB::statement('delete from  users_series where series_id = ?', [$series->id]);

            $series->delete();
            DB::commit();

            return response()->json(['message' => 'Serie and its related records deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Error deleting serie and its related records', 'error' => $e->getMessage()], 500);
        }
    }
}