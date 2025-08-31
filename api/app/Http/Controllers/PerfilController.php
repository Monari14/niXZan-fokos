<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PerfilResource;

class PerfilController extends Controller {
    public function index(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário não encontrado',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => new PerfilResource($user)
        ], 200);
    }
    public function me(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Usuário não autenticado ou token inválido',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'data'   => new PerfilResource($user),
        ], 200);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Usuário não autenticado.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:30|unique:users,username,' . $user->id,
            'email'    => 'sometimes|string|email|unique:users,email,' . $user->id,
            'bio'      => 'nullable|string|max:500',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Erro de validação.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Atualiza campos enviados (mesmo vazios)
        foreach (['name', 'username', 'email', 'bio'] as $field) {
            if ($request->has($field) || $request->exists($field)) {
                $user->$field = $request->input($field);
            }
        }

        // Atualiza avatar se enviado
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('a', 'public');
            $user->avatar = $avatarPath;
        }

        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Usuário atualizado com sucesso.',
            'data'    => new PerfilResource($user),
        ], 200);
    }

    public function avatar(Request $request) {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Usuário não autenticado.'
            ], 401);
        }
        return response()->json([
            'usuario' => [
                'username' => $user->username,
                'avatar' => url($user->avatar_url),
            ],
        ]);
    }
}
