<?php

namespace App\Http\Controllers;

use App\Models\UsersHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersHistoryController extends Controller
{

    /*public function getUserChaptersHistory($user_id)
    {d
        $history = UsersHistory::where('user_id', $user_id)
            ->with([
                'chapter',
                'chapter.series' => function ($query) {
                    $query->addSelect('id', 'name');
                },
                'chapter.function_series' => function ($query) {
                    $query->addSelect('id', 'name', 'value');
                },
            ])
            ->get()
            ->map(function ($item){
              return [
                  'serie' => $item->chapter->series->name,
                  'chapter' => $item->chapter->num_chapter,
                  'function' => $item->chapter->function_series->name,
                  'value' => $item->chapter->function_series->value,
              ];
            });
        return response()->json($history);
    }*/

    public function getUserChaptersWithFunctions($userId)
    {
        try {
            // Obtener el usuario por su ID
            $user = User::findOrFail($userId);

            // Obtener todos los capítulos que el usuario ha registrado
            $chapters = $user->chapters;

            // Para cada capítulo, obtener las funciones y valores asociados en las distintas series
            $chapters->each(function ($chapter) {
                $chapter->functions = $chapter->series->functions;
            });

            return response()->json($chapters);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener los capítulos del usuario', 'error' => $e->getMessage()], 500);
        }
    }
    public function getUserFunctionTotal($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $history = UsersHistory::where('user_id', $user_id)
            ->with('chapter.function_series')
            ->get();

        if ($history->isEmpty()) {
            return response()->json(['message' => 'No history records found for this user'], 404);
        }

        $grouped = $history->groupBy(function ($item) {
            return $item->chapter->function_series->name;
        });

        $totals = $grouped->map(function ($items, $functionName) {
            return [
                'function' => $functionName,
                'total' => $items->sum(function ($item) {
                    return $item->chapter->function_series->value;
                }),
            ];
        })->values();

        $totalGeneral = $totals->sum('total');

        return response()->json([
            'totals' => $totals,
            'total_general' => $totalGeneral,
        ]);
    }

    public function getAllUsersWithTotalValue()
    {
        try {
            // Subquery to calculate total_a_pagar
            $totalAPagarSubQuery = DB::table('chapters as c')
                ->selectRaw('COALESCE(SUM(CASE WHEN c.is_divided = 1 THEN c.value / 2 ELSE c.value END), 0)')
                ->whereYear('c.created_at', '=', date('Y'))
                ->whereMonth('c.created_at', '=', date('m'))
                ->whereColumn('c.user_id', 'u.id')
                ->groupBy('c.user_id');

            // Main query
            $results = DB::table('users as u')
                ->leftJoin('chapters as c', function ($join) {
                    $join->on('u.id', '=', 'c.user_id')
                        ->whereYear('c.created_at', '=', date('Y'))
                        ->whereMonth('c.created_at', '=', date('m'));
                })
                ->leftJoin('type_function as tf', 'c.function_series_id', '=', 'tf.id')
                ->leftJoin('series as s', 'c.series_id', '=', 's.id')
                ->select('u.id as user_id', 'u.username as user_name', 'c.num_chapter as capitulos', 'tf.name as Funciones', 's.name as Serie')
                ->selectSub($totalAPagarSubQuery, 'total_a_pagar')
                ->groupBy('u.id', 'c.num_chapter', 'tf.name', 's.name', 'c.is_divided', 'c.value')
                ->orderBy('u.id')
                ->orderBy('c.num_chapter')
                ->get();

            // Convert the results to a collection and group by 'user_name'
            $groupedResults = collect($results)->groupBy('user_name');

            // Map through each group and create a new structure
            $groupedResults = $groupedResults->map(function ($group, $userName) {
                $totalAPagar = $group->first()->total_a_pagar;
                $group->each(function ($item) {
                    unset($item->user_name, $item->total_a_pagar);
                });
                return [
                    'total_a_pagar' => $totalAPagar,
                    'data' => $group->values()
                ];
            });

            return response()->json($groupedResults, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error getting users', 'error' => $e->getMessage()], 500);
        }
    }
    public function exportExcelTotalAño()
    {
        try {
            $results = collect();

            // Iterate over each month of the current year
            for ($month = 1; $month <= 12; $month++) {
                // Subquery to calculate total_a_pagar
                $totalAPagarSubQuery = DB::table('chapters as c')
                    ->selectRaw('COALESCE(SUM(CASE WHEN c.is_divided = 1 THEN c.value / 2 ELSE c.value END), 0)')
                    ->whereYear('c.created_at', '=', date('Y'))
                    ->whereMonth('c.created_at', '=', $month)
                    ->whereColumn('c.user_id', 'u.id')
                    ->groupBy('c.user_id');

                // Main query
                $monthlyResults = DB::table('users as u')
                    ->leftJoin('chapters as c', function ($join) use ($month) {
                        $join->on('u.id', '=', 'c.user_id')
                            ->whereYear('c.created_at', '=', date('Y'))
                            ->whereMonth('c.created_at', '=', $month);
                    })
                    ->leftJoin('type_function as tf', 'c.function_series_id', '=', 'tf.id')
                    ->leftJoin('series as s', 'c.series_id', '=', 's.id')
                    ->select('u.id as user_id', 'u.username as user_name', 'c.num_chapter as capitulos', 'tf.name as Funciones', 's.name as Serie')
                    ->selectSub($totalAPagarSubQuery, 'total_a_pagar')
                    ->groupBy('u.id', 'c.num_chapter', 'tf.name', 's.name', 'c.is_divided', 'c.value')
                    ->orderBy('u.id')
                    ->orderBy('c.num_chapter')
                    ->get();

                // If no results were found for the month, continue to the next month
                if ($monthlyResults->isEmpty()) {
                    continue;
                }

                // Convert the results to a collection and group by 'user_name'
                $groupedResults = collect($monthlyResults)->groupBy('user_name');

                // Map through each group and create a new structure
                $groupedResults = $groupedResults->map(function ($group, $userName) {
                    $totalAPagar = $group->first()->total_a_pagar;
                    $group->each(function ($item) {
                        unset($item->user_name, $item->total_a_pagar);
                    });
                    return [
                        'total_a_pagar' => $totalAPagar,
                        'data' => $group->values()
                    ];
                });

                $results->put("Month {$month}", $groupedResults);
            }

            return response()->json($results, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error getting users', 'error' => $e->getMessage()], 500);
        }
    }
    public function exportExcelYearMonth(Request $request)
    {
        try {
            $month = $request->month;
            $year = $request->year;

            // Check if there are any records for the given year and month
            $recordExists = DB::table('chapters as c')
                ->whereYear('c.created_at', '=', $year)
                ->whereMonth('c.created_at', '=', $month)
                ->exists();

            if (!$recordExists) {
                return response()->json(['error' => 'No hay registros para el año y mes proporcionados'], 404);
            }

            // Subquery to calculate total_a_pagar
            $totalAPagarSubQuery = DB::table('chapters as c')
                ->selectRaw('COALESCE(SUM(c.value), 0)') // Modificado aquí
                ->whereYear('c.created_at', '=', $year)
                ->whereMonth('c.created_at', '=', $month)
                ->whereColumn('c.user_id', 'u.id')
                ->groupBy('c.user_id');

            // Main query
            $results = DB::table('users as u')
                ->leftJoin('chapters as c', function ($join) use ($year, $month) {
                    $join->on('u.id', '=', 'c.user_id')
                        ->whereYear('c.created_at', '=', $year)
                        ->whereMonth('c.created_at', '=', $month);
                })
                ->leftJoin('type_function as tf', 'c.function_series_id', '=', 'tf.id')
                ->leftJoin('series as s', 'c.series_id', '=', 's.id')
                ->select('u.id as user_id', 'u.username as user_name', 'c.num_chapter as capitulos', 'tf.name as Funciones', 's.name as Serie')
                ->selectSub($totalAPagarSubQuery, 'total_a_pagar')
                ->groupBy('u.id', 'c.num_chapter', 'tf.name', 's.name', 'c.is_divided', 'c.value')
                ->havingRaw('total_a_pagar > 0') // Exclude users with total_a_pagar as null or 0
                ->orderBy('u.id')
                ->orderBy('c.num_chapter')
                ->get();

            // Convert the results to a collection and group by 'user_name'
            $groupedResults = $results->groupBy('user_name');

            // Map through each group and create a new structure
            $groupedResults = $groupedResults->map(function ($group, $userName) {
                $totalAPagar = $group->first()->total_a_pagar;
                $group->each(function ($item) {
                    unset($item->user_name, $item->total_a_pagar);
                });
                return [
                    'total_a_pagar' => $totalAPagar,
                    'data' => $group->values()
                ];
            });

            return response()->json($groupedResults, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error getting users', 'error' => $e->getMessage()], 500);
        }
    }
}
