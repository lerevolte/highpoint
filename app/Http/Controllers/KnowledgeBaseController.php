<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    /**
     * Находит статью по ее slug и возвращает в формате JSON.
     * Используется для динамической подгрузки в модальное окно.
     */
    public function fetchArticle(Request $request)
    {
        $request->validate(['slug' => 'required|string|exists:knowledge_base_articles,slug']);

        $article = KnowledgeBaseArticle::where('slug', $request->slug)->first();

        return response()->json([
            'title' => $article->title,
            'content' => $article->content,
        ]);
    }
}
