<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreFeaturedBannerRequest;
use App\Http\Requests\Api\V1\Admin\UpdateFeaturedBannerRequest;
use App\Http\Resources\FeaturedBannerResource;
use App\Models\FeaturedBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * @tags Admin / Featured Banners
 */
class FeaturedBannerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', FeaturedBanner::class);

        $banners = FeaturedBanner::query()
            ->with('media')
            ->when($request->input('search'), fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->has('filter.is_active'), fn ($q) => $q->where('is_active', $request->boolean('filter.is_active')))
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return FeaturedBannerResource::collection($banners);
    }

    public function show(FeaturedBanner $featuredBanner): FeaturedBannerResource
    {
        $this->authorize('view', $featuredBanner);

        $featuredBanner->load('media');

        return new FeaturedBannerResource($featuredBanner);
    }

    public function store(StoreFeaturedBannerRequest $request): JsonResponse
    {
        $banner = FeaturedBanner::create($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $banner->addMediaFromRequest('image')->toMediaCollection('image');
        }

        $banner->load('media');

        Cache::forget('featured-banners:active');

        return (new FeaturedBannerResource($banner))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateFeaturedBannerRequest $request, FeaturedBanner $featuredBanner): FeaturedBannerResource
    {
        $featuredBanner->update($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $featuredBanner->addMediaFromRequest('image')->toMediaCollection('image');
        }

        $featuredBanner->load('media');

        Cache::forget('featured-banners:active');

        return new FeaturedBannerResource($featuredBanner);
    }

    public function destroy(FeaturedBanner $featuredBanner): JsonResponse
    {
        $this->authorize('delete', $featuredBanner);

        $featuredBanner->delete();

        Cache::forget('featured-banners:active');

        return response()->json(['message' => 'Featured banner deleted.'], 200);
    }
}
