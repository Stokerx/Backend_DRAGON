<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\Request;
use App\Models\FunctionSerie;
use App\Models\Series;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Validation\Rule;

class ChapterController extends Controller
{
    public function createChapter(Request $request, Series $series, Chapter $chapter, FunctionSerie $functionSerie, ActivitySeriesUserController $activityController)
    {
        try{
            $request->validate([
                'series_id' => 'required|integer|exists:series,id',
                'function_series_id' => [
                    'required',
                    'integer',
                    Rule::exists('function_series', 'id')->where(function ($query) use ($request) {
                        $query->where('series_id', $request->series_id);
                    }),
                ],
                'is_divided' => 'required|boolean',
                'num_chapter' => 'required|numeric|regex:/^\d+(\.\d{1})?$/',
            ]);

            $userId = Auth::id();

            $series = $series->findOrFail($request->series_id);

            DB::beginTransaction();

            $functionSerie = $functionSerie->findOrFail($request->function_series_id);
            $functionValue = $functionSerie->value; // Obtiene el valor de la función

            if(!$request->is_divided) {
                $chapterExists = $chapter->where('series_id', $request->series_id)
                    ->where('function_series_id', $request->function_series_id)
                    ->where('num_chapter', $request->num_chapter)
                    ->exists();

                if ($chapterExists) {
                    return response()->json(['message' => 'Ya has registrado este capitulo con la misma funcion y no esta dividido.'], 400);
                }
            } else {
                // Si el capítulo está dividido, ajusta el valor de la función según sea necesario
                // Por ejemplo, si necesitas dividir el valor por la mitad para capítulos divididos:
                $functionValue /= 2; // Ajusta según la lógica de negocio
            }

            $user = User::findOrFail($userId);
            // Añade el valor de la función al capítulo al crear el registro
            $chapterData = $request->all();
            $chapterData['value'] = $functionValue; // Asegúrate de que la columna en la tabla chapters se llame 'value'
            $chapter = $user->chapters()->create($chapterData);

            $activityController->insertActivitySeriesUser($userId, $series->id, $chapter->id);

            DB::commit();

            return response()->json(['message' => 'Capitulo registrado', 'chapter' => $chapter], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'La serie no se encontró', 'error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error al crear el capitulo', 'error' => $e->getMessage()], 500);
        }
    }
}