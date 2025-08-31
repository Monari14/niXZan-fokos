<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Http\Resources\NewsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewLiked;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::with(['user'])
            ->latest()
            ->paginate(10);

        return NewsResource::collection($news);
    }

    public function show($id_new)
    {
        $news = News::with(['user'])->findOrFail($id_new);
        return new NewsResource($news);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if(!$user){
            return response()->json([
                'error' => 'Usuário não autenticado.'
            ], 401);
        }

        try {
            $validated = $request->validate([
                'title'   => 'required|string|max:255',
                'content' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => 'Erro de validação.',
                'details' => $e->errors()
            ], 422);
        }

        try {
            $validated['id_user'] = $user->id;
            $news = News::create($validated);

            return new NewsResource($news);
        } catch (QueryException $e) {
            return response()->json([
                'error'   => 'Erro ao salvar no banco de dados.',
                'details' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Erro inesperado.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id_new)
    {
        $news = News::findOrFail($id_new);

        if ($news->id_user !== Auth::id()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'title'   => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $news->update($validated);
        $news->load('user');

        return new NewsResource($news);
    }

    public function destroy($id_new)
    {
        $news = News::findOrFail($id_new);

        if ($news->id_user !== Auth::id()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $news->delete();

        return response()->json(['message' => 'Fok removido com sucesso']);
    }
    public function like(Request $request, $id_new)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.'
            ], 401);
        }

        $news = News::find($id_new);
        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'Fok não encontrado.'
            ], 404);
        }

        if ($news->id_user === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode curtir seu próprio Fok.'
            ], 403);
        }

        if ($news->likes()->where('id_user', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Você já curtiu este Fok.'
            ], 400);
        }

        try {
            $news->likes()->create([
                'id_user' => $user->id,
            ]);

            try {
                $news->user->notify(new NewLiked($user, $news->id));
            } catch (\Exception $e) {
                \Log::warning('Falha ao enviar notificação de like: '.$e->getMessage());
            }

            $news->loadCount('likes');

            return response()->json([
                'success' => true,
                'message' => 'Fok curtido com sucesso!',
                'likes'   => $news->likes_count
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao curtir o Fok.',
                'errors'  => $e->getMessage()
            ], 500);
        }
    }
    public function unlike(Request $request, $id_new)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.'
            ], 401);
        }

        $news = News::find($id_new);
        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'Fok não encontrado.'
            ], 404);
        }

        $like = $news->likes()->where('id_user', $user->id)->first();
        if (!$like) {
            return response()->json([
                'success' => false,
                'message' => 'Você não curtiu este Fok.'
            ], 400);
        }

        try {
            $like->delete();

            $news->loadCount('likes');

            return response()->json([
                'success' => true,
                'message' => 'Curtida removida com sucesso.',
                'likes'   => $news->likes_count
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover curtida.',
                'errors'  => $e->getMessage()
            ], 500);
        }
    }
}
