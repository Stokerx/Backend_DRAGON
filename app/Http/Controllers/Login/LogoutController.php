<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class LogoutController extends Controller
{
    /**
     * Logout
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request)
    {
        // Verificar si hay un usuario autenticado
        if (auth()->guard()->check()) {
            // Obtener el token actual del usuario autenticado
            $token = $request->user()->currentAccessToken();

            // Verificar si se encontró el token
            if ($token) {
                // Obtener el nombre del token
                $tokenName = $token->name;

                // Eliminar el token
                $token->delete();

                return response()->json(['message' => 'Token eliminado exitosamente', 'tokenName' => $tokenName], 200);
            } else {
                return response()->json(['error' => 'No se encontró el token asociado al usuario'], 404);
            }
        } else {
            return response()->json(['error' => 'No hay ningún usuario autenticado'], 404);
        }
    }
}