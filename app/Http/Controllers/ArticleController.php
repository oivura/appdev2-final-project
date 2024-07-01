<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::all();
        return response()->json([
            'data' => $articles,
        ]);
    }

    public function show($id)
    {
        $article = Article::findOrFail($id);
        return response()->json([
            'data' => $article,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $article = Article::create([
            'user_id' => auth()->id(),  
            'title' => $request->input('title'),
            'content' => $request->input('content'),
        ]);

        return response()->json($article, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
        ]);

        $article = Article::findOrFail($id);

        // Check if the authenticated user is the owner of the article
        if ($article->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to update this article'], 403);
        }

        $article->update($request->only(['title', 'content']));

        return response()->json($article);
    }
   
   
    public function destroy($id)
    {
        $article = Article::findOrFail($id);

        // Check if the authenticated user is the owner of the article
        if ($article->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to delete this article'], 403);
        }

        $article->delete();

        return response()->json(['message' => 'Article successfully deleted'], 200);
    }
}
