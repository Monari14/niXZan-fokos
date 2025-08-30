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
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
        ]);

        $validated['id_user'] = $user->id;

        $news = News::create($validated);

        return new NewsResource($news);
    }

    public function update(Request $request, $id_new)
    {
        $news = News::findOrFail($id_new);

        if ($news->id_user !== Auth::id()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'content'     => 'sometimes|string',
        ]);

        $news->update($validated);

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
        $new = News::find($id_new);
        if (!$new) {
            return response()->json(['message' => 'Fok não encontrado.'], 404);
        }

        $alreadyLiked = $new->likes()->where('id_user', $request->user()->id)->exists();

        if ($alreadyLiked) {
            return response()->json(['message' => 'Você já curtiu este fok.'], 400);
        }

        $new->likes()->create([
            'id_user' => $request->user()->id,
        ]);

        $new->user->notify(new NewLiked($request->user(), $new->id));

        return response()->json(['message' => 'Fok curtido!']);
    }

    public function unlike(Request $request, $id_new)
    {
        $new = News::find($id_new);
        if (!$new) {
            return response()->json(['message' => 'Fok não encontrado.'], 404);
        }

        $like = $new->likes()->where('id_user', $request->user()->id)->first();

        if (!$like) {
            return response()->json(['message' => 'Você não curtiu este fok.'], 400);
        }

        $like->delete();

        return response()->json(['message' => 'Curtida removida.']);
    }
}
