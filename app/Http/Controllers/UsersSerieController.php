<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UsersSerie;
use Illuminate\Http\Request;

class UsersSerieController extends Controller
{
    public function assignSeriesToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'series_id' => 'required|exists:series,id',
        ]);

        UsersSerie::create([
            'user_id' => $request->user_id,
            'series_id' => $request->series_id,
        ]);

        return response()->json(['message' => 'Serie asignada al usuario'], 201);
    }

    public function unassignSeriesToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'series_id' => 'required|exists:series,id',
        ]);

        $userSeries = UsersSerie::where('user_id', $request->user_id)
            ->where('series_id', $request->series_id)
            ->first();
        if (!$userSeries) {
            return response()->json(['message' => 'La serie no esta asignada al usuario'], 400);
        }

        $userSeries->delete();

        return response()->json(['message' => 'Serie desasignada al usuario'], 200);
    }

    public function getAssignedAndUnassignedUsers($serieId)
    {
        try {
            // Get all users assigned to the series
            $assignedUsers = UsersSerie::where('series_id', $serieId)
                ->join('users', 'users.id', '=', 'users_series.user_id')
                ->select('users.*')
                ->get();

            // Modify the assignedUsers collection to include the full URL of img_perfil
            $assignedUsers->each(function ($user) {
                $user->img_perfil = env('APP_URL') . '/storage/' . $user->img_perfil;
            });

            // Get all users not assigned to the series
            $unassignedUsers = User::whereNotIn('id', function ($query) use ($serieId) {
                $query->select('user_id')
                    ->from('users_series')
                    ->where('series_id', $serieId);
            })->get();

            // Modify the unassignedUsers collection to include the full URL of img_perfil
            $unassignedUsers->each(function ($user) {
                $user->img_perfil = env('APP_URL') . '/storage/' . $user->img_perfil;
            });

            return response()->json([
                'assigned_users' => $assignedUsers,
                'unassigned_users' => $unassignedUsers
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error getting users', 'error' => $e->getMessage()], 500);
        }
    }
}
