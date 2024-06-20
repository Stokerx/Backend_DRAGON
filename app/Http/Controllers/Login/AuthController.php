<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        if (!Auth::guard()->once($credentials)) {
            throw ValidationException::withMessages([
                'username' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $user = Auth::guard()->user();

        if (!$user->status) {
            throw ValidationException::withMessages([
                'username' => ['Este usuario está desactivado.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken($request->username)->plainTextToken,
            'img_perfil' => env('APP_URL') . Storage::url($user->img_perfil),
            'role' => $user->getRoleNames()->first(),
            'username' => $user->username,
        ]);
    }
}