<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

        $news = News::with('fotos')
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->get();

        $momentosFormatados = $news->map(function ($momento) {
            return [
                'id' => $momento->id,
                'descricao' => $momento->descricao ?? '',
                'fotos' => $momento->fotos->map(function ($foto) {
                    return [
                        'id' => $foto->id,
                        'url' => url($foto->foto_url),
                    ];
                }),
                'data_completa' => $momento->created_at->toDateTimeString(),
                'data' => $momento->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'status' => 200,
            'dados' => [
                'usuario' => [
                    'id' => $user->id,
                    'nome' => $user->name,
                    'username' => $user->username,
                    'bio' => $user->bio ?? '',
                    'avatar_url' => url($user->avatar_url),
                    'stats' => [
                        'seguindo' => $user->following()->count(),
                        'seguidores' => $user->followers()->count(),
                        'mober_count' => $user->momentos()->count(),
                        'likes_count' => $user->likes()->count(),
                    ],
                ],
                'momentos' => $momentosFormatados,
            ],
        ]);
    }
}
