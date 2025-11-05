<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);

        $newsItems = News::query()
            ->where('is_public', true)
            ->orderByDesc('created_at')
            ->limit($limit > 0 ? $limit : 10)
            ->get(['id', 'title', 'content', 'created_at', 'updated_at']);

        return response()->json([
            'news' => $newsItems,
        ]);
    }
}
