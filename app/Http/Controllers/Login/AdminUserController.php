<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use App\Models\Chapter;

class AdminUserController extends Controller
{
    public function createUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
            'img_perfil' => 'image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        $user = new User([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'status' => 1,
        ]);

        if($request->hasFile('img_perfil')){
            $user->img_perfil = $this->storeProfileImage($request);
        }

        $user->save();

        // Asignar el rol al usuario
        $user->assignRole($request->role);

        return response()->json(['message' => 'User created and role assigned']);
    }

    private function storeProfileImage(Request $request)
    {
        $file_name_perfil = $request->file('img_perfil')->store('perfil_image','public');
        return $file_name_perfil;
    }

    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'username' => 'string|max:255',
            'role' => 'string|exists:roles,name',
            'img_perfil' => 'image|mimes:jpeg,png,jpg,svg,webp|max:3068',
            'password' => 'string|min:8',
        ]);

        $user = User::findOrFail($id);

        if($request->filled('username')){
            $user->username = $request->username;
        }

        // Asignar el rol al usuario
        if($request->filled('role')){
            $user->syncRoles($request->role);
        }

        if($request->hasFile('img_perfil')){
            // Delete the old profile image
            if($user->img_perfil) {
                Storage::disk('public')->delete($user->img_perfil);
            }

            $user->img_perfil = $this->storeProfileImage($request);
        }

        $user->save();

        return response()->json(['message' => 'User updated']);
    }


    public function desactiveUsers(Request $request, $id)
    {
        if(!$request->user()->hasRole('admin')){
            return response()->json(['message' => 'no autorizado'], 403);
        }

        $user = User::findOrFail($id);
        $user->update(['status' => 0]);

        $user->tokens()->delete();

        return response()->json(['message' => 'Usuario desactivado']);
    }

    public function getUserProfile(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'img_perfil' => $user->img_perfil ? env('APP_URL') . Storage::url($user->img_perfil) : null,
            'role' => $user->getRoleNames()->first(),
            'username' => $user->username,
        ]);
    }

    public function reactiveUsers(Request $request, $id)
    {
        if(!$request->user()->hasRole('admin')){
            return response()->json(['message' => 'no autorizado'], 403);
        }

        $user = User::findOrFail($id);
        $user->update(['status' => 1]);

        return response()->json(['message' => 'Usuario reactivado']);
    }


    public function updateProfileUser(Request $request)
    {
        $request->validate([
            'username' => 'string|max:255',
            'img_perfil' => 'image|mimes:jpeg,png,jpg,svg,webp|max:3072',
            'new_password' => 'string|min:8|nullable',
            'confirm_new_password' => 'same:new_password',
        ]);

        $user = Auth::user();

        if($request->filled('username')){
            $user->username = $request->username;
        }

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        if($request->hasFile('img_perfil')){
            // Delete the old profile image
            if($user->img_perfil) {
                Storage::disk('public')->delete($user->img_perfil);
            }

            $user->img_perfil = $this->storeProfileImage($request);
        }

        $user->save();

        $user->img_perfil = env('APP_URL') . '/storage/' . $user->img_perfil;

        return response()->json($user);
    }
}