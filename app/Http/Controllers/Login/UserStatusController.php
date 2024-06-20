<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserStatusController extends Controller
{
    public function getUserStatus(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        return response()->json([
            'username' => $user->username,
            'status' => $user->status,
            'roles' => $user->roles
        ]);
    }
}