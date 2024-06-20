<?php

namespace App\Http\Controllers;

use App\Models\ActivitySeriesUser;
use App\Models\Chapter;
use App\Models\FunctionSerie;
use App\Models\Series;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ActivitySeriesUserController extends Controller
{
    public function getLastUserActivites() // function that returns the list of series registration of all users
    {
        $activities = ActivitySeriesUser::selectRaw('activity_series_users.created_at, activity_series_users.updated_at, activity_series_users.description, users.id as user_id, users.username as user_name, CASE WHEN users.img_perfil LIKE "' . env('APP_URL') . '%" THEN users.img_perfil ELSE CONCAT("' . env('APP_URL') . '/storage/' . '", users.img_perfil) END as user_image')
            ->join('users', 'activity_series_users.user_id', '=', 'users.id')
            ->orderBy('activity_series_users.updated_at', 'desc')
            ->get();
        return response()->json($activities);
    }

    public function getLastUserActiviteGeneral()
    {
        $activities = ActivitySeriesUser::selectRaw('activity_series_users.created_at, 
    activity_series_users.updated_at, activity_series_users.description, users.id as user_id, 
    users.username as user_name, CASE WHEN users.img_perfil LIKE "http%" THEN users.img_perfil ELSE CONCAT("' . env('APP_URL') . '/storage/' . '", users.img_perfil) END as user_image, series.id as series_id, series.name as series_name')
            ->join('users', 'activity_series_users.user_id', '=', 'users.id')
            ->join('series', 'activity_series_users.series_id', '=', 'series.id')
            ->where('activity_series_users.description', 'like', '%serie%')
            ->orwhere('activity_series_users.description', 'like', '%capitulo%')
            ->orwhere('activity_series_users.description', 'like', '%valor%')
            ->orderBy('activity_series_users.updated_at', 'desc')
            ->get();

        return response()->json($activities);
    }


    public function insertActivitySeriesUser($userId, $seriesId, $chapterId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $userName = $user->username; // Cambiado de $user->name a $user->username
        $chapter = Chapter::findOrfail($chapterId);
        $chapterNumber = $chapter->num_chapter;
        $functionName = $chapter->function->name; // Accede a la función del capítulo
        $serieName = Series::findOrfail($seriesId)->name;

        $activity = ActivitySeriesUser::create([
            'user_id' => $userId,
            'series_id' => $seriesId,
            'chapter_id' => $chapterId,
            'is_system_activity' => 0,
            'description' => "El usuario $userName ha registrado la función $functionName del capitulo $chapterNumber, de  $serieName"
        ]);

        return response()->json($activity);
    }

    public function getSeriesRegistrations($seriesId)
    {
        $activities = ActivitySeriesUser::where('series_id', $seriesId)
            ->where('description', 'like', '%ha registrado el capitulo%')
            ->get();

        return response()->json($activities);
    }

    public function insertActivitySeriesUserspecify($userId, $seriesId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $userName = $user->username; // Cambiado de $user->name a $user->username
        $serieName = Series::findOrfail($seriesId)->name;

        $activity = ActivitySeriesUser::create([
            'user_id' => $userId,
            'series_id' => $seriesId,
            'is_system_activity' => 0,
            'description' => "El usuario $userName ha creado la serie $serieName"
        ]);

        return response()->json($activity);
    }

    public function insertActivitySeriesUserspecifydelete($userId, $seriesId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $userName = $user->username; // Cambiado de $user->name a $user->username
        $serieName = Series::findOrfail($seriesId)->name;

        $activity = ActivitySeriesUser::create([
            'user_id' => $userId,
            'series_id' => $seriesId,
            'is_system_activity' => 0,
            'description' => "El usuario $userName ha eliminado la serie $serieName"
        ]);

        return response()->json($activity);
    }

    public function insertActivityChapterUsersSpecifydelete($userId, $chapterId, $seriesId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $userName = $user->username; // Cambiado de $user->name a $user->username
        $chapterNumber = Chapter::findOrfail($chapterId)->num_chapter;
        $serieName = Series::findOrfail($seriesId)->name;


        $activity = ActivitySeriesUser::create([
            'user_id' => $userId,
            'chapter_id' => $chapterId,
            'is_system_activity' => 0,
            'description' => "El usuario $userName ha eliminado el capitulo $chapterNumber de la serie $serieName del usuario $userName"
        ]);

        return response()->json($activity);
    }

    public function insertActivityUpdateValueSeries($userId, $seriesId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $userName = $user->username; // Cambiado de $user->name a $user->username
        $serieName = Series::findOrfail($seriesId)->name;

        $activity = ActivitySeriesUser::create([
            'user_id' => $userId,
            'series_id' => $seriesId,
            'is_system_activity' => 0,
            'description' => "El usuario $userName ha actualizado el valor de la serie $serieName"
        ]);

        return response()->json($activity);
    }

    public function getListUsers(Request $request)
    {
        $user = $request->user();
        $isAdminOrDragon = $user->hasRole('admin') || $user->hasRole('dragon');

        $totalAPagar = DB::table('chapters as c')
            ->selectRaw('COALESCE(SUM(c.value), 0)') // Modificado aquí
            ->whereYear('c.created_at', '=', date('Y'))
            ->whereMonth('c.created_at', '=', date('m'))
            ->whereColumn('c.user_id', 'users.id');

        $query = User::select('id', 'username', DB::raw("CONCAT('" . env('APP_URL') . '/' . 'storage/' . "', img_perfil) AS img_perfil"), 'status')
            ->selectSub($totalAPagar, 'total_a_pagar')
            ->with(['roles' => function ($query) {
                $query->select('id', 'name');
            }]);

        if (!$isAdminOrDragon) {
            $query->where('id', $user->id);
        }

        $users = $query->get();

        // Eliminar el conjunto "pivot" de la respuesta
        foreach ($users as $user) {
            foreach ($user->roles as $role) {
                unset($role->pivot);
            }
        }

        return response()->json($users);
    }


    public function getSumFunctionValueByType($userId)
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $userDetails = User::select([
            'users.id as user_id',
            'users.username as user_name',
            'type_function.name as tipo_labor',
            DB::raw('COUNT(chapters.id) as cantidad_capitulos'),
            DB::raw('COALESCE(SUM(chapters.value), 0) as total_a_pagar'), // Modificado aquí
            DB::raw("DATE_FORMAT(CURDATE(), '%Y-%m') as mes_anio")
        ])
            ->join('chapters', 'users.id', '=', 'chapters.user_id')
            ->join('function_series', 'chapters.function_series_id', '=', 'function_series.id')
            ->join('type_function', 'function_series.type_id', '=', 'type_function.id')
            ->where('users.id', $userId)
            ->whereYear('chapters.created_at', $currentYear)
            ->whereMonth('chapters.created_at', $currentMonth)
            ->groupBy('users.id', 'users.username', 'type_function.name')
            ->get();

        return $userDetails;
    }


    public function getTotalSum($userId)
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $userDetails = User::select([
            'users.id as user_id',
            'users.username as user_name',
            DB::raw('COUNT(chapters.id) as total_capitulos'),
            DB::raw('COALESCE(SUM(chapters.value), 0) as total_a_pagar'),
            DB::raw("DATE_FORMAT(CURDATE(), '%Y-%m') as mes_anio")
        ])
            ->leftJoin('chapters', function ($join) use ($currentYear, $currentMonth) {
                $join->on('users.id', '=', 'chapters.user_id')
                    ->whereYear('chapters.created_at', $currentYear)
                    ->whereMonth('chapters.created_at', $currentMonth);
            })
            ->where('users.id', $userId)
            ->groupBy('users.id')
            ->first(); // Usamos first() si esperamos un solo registro por usuario

        return $userDetails;
    }


    public function GetUserActivityCurrentMonth($userId)
    {
        $activities = Chapter::from('chapters as c')
            ->select(
                's.name as series_name',
                'c.num_chapter as chapter_number',
                'tf.name as function_name',
                'c.value as chapter_value', // Modificado aquí
                'asu.id as activity_id' // Agregar el ID de la actividad
            )
            ->join('series as s', 'c.series_id', '=', 's.id')
            ->join('function_series as fs', 'c.function_series_id', '=', 'fs.id')
            ->join('type_function as tf', 'fs.type_id', '=', 'tf.id')
            ->join('activity_series_users as asu', function ($join) {
                $join->on('asu.series_id', '=', 'c.series_id')
                    ->on('asu.chapter_id', '=', 'c.id');
            })
            ->where('c.user_id', $userId)
            ->whereMonth('c.created_at', date('m'))
            ->whereYear('c.created_at', date('Y'))
            ->get();

        return $activities;
    }


    //eliminar registro especifico del capitulo que el usuario hizo de usuario
    public function deleteUserActivity($activityId)
    {
        $activity = ActivitySeriesUser::find($activityId);
        if (!$activity) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

        $chapterId = $activity->chapter_id;

        // Delete the chapter associated with the activity
        DB::unprepared("DELETE FROM chapters WHERE id = $chapterId");

        // Delete the activity
        $activity->delete();

        return response()->json(['message' => 'Activity and associated chapter deleted']);
    }

}

