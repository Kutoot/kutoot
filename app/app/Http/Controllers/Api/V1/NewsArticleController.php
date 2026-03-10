<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsArticleResource;
use App\Models\NewsArticle;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * @tags News Articles
 */
class NewsArticleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $articles = Cache::remember('news-articles:active', 300, function () {
            return NewsArticle::query()
                ->with('media')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('published_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        });

        return NewsArticleResource::collection($articles);
    }
}
