<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\News;
use Illuminate\Http\Request;
use App\Notifications\NewCommented;

class CommentController extends Controller
{

    public function store(Request $request, $id_new)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $new = News::find($id_new);

        if (!$new) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mober não encontrado.',
            ], 404);
        }

        $comment = Comment::create([
            'id_new' => $id_new,
            'id_user' => $request->user()->id,
            'content' => $request->input('content'),
        ]);

        // Notifica que o post foi comentado
        $new->usuario->notify(new NewCommented($request->user(), $new->id));

        // Carrega o relacionamento do usuário para já retornar junto
        $comment->load('user:id,username,avatar');

        return response()->json([
            'status' => 'success',
            'message' => 'Comentário adicionado com sucesso!',
            'data' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->diffForHumans(),
                'user' => [
                    'id' => $comment->user->id,
                    'username' => $comment->user->username,
                    'avatar_url' => $comment->user->avatar_url
                ]
            ]
        ], 201);
    }

    public function index($id_new)
    {
        $new = News::find($id_new);

        if (!$new) {
            return response()->json([
                'message' => 'Fok não encontrado.',
            ], 404);
        }

        $comments = $new->comments()
            ->with('user:id,username,avatar')
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->diffForHumans(),
                    'user' => [
                        'id' => $comment->user->id,
                        'username' => $comment->user->username,
                        'avatar_url' => $comment->user->avatar_url
                    ]
                ];
            });

        return response()->json([
            'message' => 'Comentários carregados com sucesso.',
            'data' => $comments
        ], 200);
    }

    public function destroy(Request $request, $id_comment)
    {
        $comment = Comment::find($id_comment);

        if (!$comment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Comentário não encontrado.',
            ], 404);
        }

        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Não autorizado.',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Comentário deletado com sucesso.',
        ], 200);
    }

}
