<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Http\Resources\NewsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewLiked;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return NewsResource::collection($news);
    }

    public function show($id)
    {
        $news = News::with(['user'])->findOrFail($id);
        return new NewsResource($news);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
        ]);

        $validated['user_id'] = $user->id;

        $news = News::create($validated);

        return new NewsResource($news);
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        if ($news->user_id !== Auth::id()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'content'     => 'sometimes|string',
        ]);

        $news->update($validated);

        return new NewsResource($news);
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);

        if ($news->user_id !== Auth::id()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $news->delete();

        return response()->json(['message' => 'Notícia removida com sucesso']);
    }

    public function like(Request $request, $id_new)
    {
        $new = News::find($id_new);
        if (!$new) {
            return response()->json(['message' => 'Mober não encontrado.'], 404);
        }

        $alreadyLiked = $new->likes()->where('user_id', $request->user()->id)->exists();

        if ($alreadyLiked) {
            return response()->json(['message' => 'Você já curtiu este mober.'], 400);
        }

        $new->likes()->create([
            'user_id' => $request->user()->id,
        ]);

        // Notifica o like
        $new->user->notify(new NewLiked($request->user(), $new->id));

        return response()->json(['message' => 'Mober curtido!']);
    }

    public function unlike(Request $request, $id_new)
    {
        $new = News::find($id_new);
        if (!$new) {
            return response()->json(['message' => 'Mober não encontrado.'], 404);
        }

        $like = $new->likes()->where('user_id', $request->user()->id)->first();

        if (!$like) {
            return response()->json(['message' => 'Você não curtiu este mober.'], 400);
        }

        $like->delete();

        return response()->json(['message' => 'Curtida removida.']);
    }
}
