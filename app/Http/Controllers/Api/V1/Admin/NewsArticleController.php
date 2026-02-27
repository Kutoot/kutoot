<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreNewsArticleRequest;
use App\Http\Requests\Api\V1\Admin\UpdateNewsArticleRequest;
use App\Http\Resources\NewsArticleResource;
use App\Models\NewsArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * @tags Admin / News Articles
 */
class NewsArticleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', NewsArticle::class);

        $articles = NewsArticle::query()
            ->with('media')
            ->when($request->input('search'), fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->has('filter.is_active'), fn ($q) => $q->where('is_active', $request->boolean('filter.is_active')))
            ->orderBy('sort_order')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return NewsArticleResource::collection($articles);
    }

    public function show(NewsArticle $newsArticle): NewsArticleResource
    {
        $this->authorize('view', $newsArticle);

        $newsArticle->load('media');

        return new NewsArticleResource($newsArticle);
    }

    public function store(StoreNewsArticleRequest $request): JsonResponse
    {
        $article = NewsArticle::create($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $article->addMediaFromRequest('image')->toMediaCollection('image');
        }

        $article->load('media');

        Cache::forget('news-articles:active');

        return (new NewsArticleResource($article))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateNewsArticleRequest $request, NewsArticle $newsArticle): NewsArticleResource
    {
        $newsArticle->update($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $newsArticle->addMediaFromRequest('image')->toMediaCollection('image');
        }

        $newsArticle->load('media');

        Cache::forget('news-articles:active');

        return new NewsArticleResource($newsArticle);
    }

    public function destroy(NewsArticle $newsArticle): JsonResponse
    {
        $this->authorize('delete', $newsArticle);

        $newsArticle->delete();

        Cache::forget('news-articles:active');

        return response()->json(['message' => 'News article deleted.'], 200);
    }
}
