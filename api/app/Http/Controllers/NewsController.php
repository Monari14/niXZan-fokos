<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Http\Resources\NewsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return NewsResource::collection($news);
    }

    public function show($id)
    {
        $news = News::with(['user', 'category'])->findOrFail($id);
        return new NewsResource($news);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category_id' => 'required|exists:categories,id',
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
            'category_id' => 'sometimes|exists:categories,id',
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
}
