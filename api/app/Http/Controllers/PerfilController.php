<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PerfilController extends Controller {
    public function index(Request $request, $username)
    {
        try {
            $user = User::where('username', $username)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário não encontrado',
            ], 404);
        }

        $news = News::all()
            ->where('id_user', $user->id)
            ->latest()
            ->get();

        $newsFormatados = $news->map(function ($new) {
            return [
                'id' => $new->id,
                'title' => $new->title,
                'content' => $new->content,
                'data_completa' => $new->created_at->toDateTimeString(),
                'data' => $new->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'dados' => [
                'usuario' => [
                    'id' => $user->id,
                    'nome' => $user->name,
                    'username' => $user->username,
                    'bio' => $user->bio ?? '',
                    'avatar_url' => url($user->avatar_url),
                    'stats' => [
                        'seguindo' => $user->seguindo()->count(),
                        'seguidores' => $user->seguidores()->count(),
                        'fok_count' => $user->noticias()->count(),
                        'likes_count' => $user->likes()->count(),
                    ],
                ],
                'foks' => $newsFormatados,
            ],
        ], 200);
    }
    public function me(Request $request)
    {
        $user = Auth::user();

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado',
            ], 404);
        }

        $news = News::all()
            ->where('id_user', $user->id)
            ->latest()
            ->get();

        $newsFormatados = $news->map(function ($new) {
            return [
                'id' => $new->id,
                'title' => $new->title,
                'content' => $new->content,
                'data_completa' => $new->created_at->toDateTimeString(),
                'data' => $new->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'dados' => [
                'usuario' => [
                    'id' => $user->id,
                    'nome' => $user->name,
                    'username' => $user->username,
                    'bio' => $user->bio ?? '',
                    'avatar_url' => url($user->avatar_url),
                    'stats' => [
                        'seguindo' => $user->seguindo()->count(),
                        'seguidores' => $user->seguidores()->count(),
                        'fok_count' => $user->noticias()->count(),
                        'likes_count' => $user->likes()->count(),
                    ],
                ],
                'foks' => $newsFormatados,
            ],
        ], 200);
    }
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
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
                'message' => 'Erro de validação.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $fields = ['name', 'username', 'email', 'bio'];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $user->$field = $request->input($field);
            }
        }

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('a', 'public');
            $user->avatar = $avatarPath;
        }

        $user->save();

        return response()->json([
            'message' => 'Usuário atualizado com sucesso.',
            'data' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'bio'      => $user->bio,
                'avatar'   => $user->avatar_url,
            ],
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
                'avatar_url' => url($user->avatar_url),
            ],
        ]);
    }
}
