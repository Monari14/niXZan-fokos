<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;
use App\Http\Resources\NewsResource;

class SearchController extends Controller
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
    public function title(Request $request)
    {
        // Validação manual para retornar todos os erros em JSON
        $validator = \Validator::make($request->all(), [
            'search' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erros de validação encontrados.',
                'errors'  => $validator->errors(), // retorna todos os erros
            ], 422);
        }

        $search = $request->input('search');

        $news = News::where('title', 'LIKE', "%{$search}%")
            ->latest('created_at') // garante ordenação pela data
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => NewsResource::collection($news),
            'meta'    => [
                'current_page' => $news->currentPage(),
                'last_page'    => $news->lastPage(),
                'per_page'     => $news->perPage(),
                'total'        => $news->total(),
            ]
        ], 200);
    }

}
