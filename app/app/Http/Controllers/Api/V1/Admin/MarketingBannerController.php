<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreMarketingBannerRequest;
use App\Http\Requests\Api\V1\Admin\UpdateMarketingBannerRequest;
use App\Http\Resources\MarketingBannerResource;
use App\Models\MarketingBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * @tags Admin / Marketing Banners
 */
class MarketingBannerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', MarketingBanner::class);

        $banners = MarketingBanner::query()
            ->with('media')
            ->when($request->input('search'), fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->has('filter.is_active'), fn ($q) => $q->where('is_active', $request->boolean('filter.is_active')))
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return MarketingBannerResource::collection($banners);
    }

    public function show(MarketingBanner $marketingBanner): MarketingBannerResource
    {
        $this->authorize('view', $marketingBanner);

        $marketingBanner->load('media');

        return new MarketingBannerResource($marketingBanner);
    }

    public function store(StoreMarketingBannerRequest $request): JsonResponse
    {
        $banner = MarketingBanner::create($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $banner->addMediaFromRequest('image')->toMediaCollection('image');
        }

        $banner->load('media');

        Cache::forget('marketing-banners:active');

        return (new MarketingBannerResource($banner))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateMarketingBannerRequest $request, MarketingBanner $marketingBanner): MarketingBannerResource
    {
        $marketingBanner->update($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $marketingBanner->addMediaFromRequest('image')->toMediaCollection('image');
        }

        $marketingBanner->load('media');

        Cache::forget('marketing-banners:active');

        return new MarketingBannerResource($marketingBanner);
    }

    public function destroy(MarketingBanner $marketingBanner): JsonResponse
    {
        $this->authorize('delete', $marketingBanner);

        $marketingBanner->delete();

        Cache::forget('marketing-banners:active');

        return response()->json(['message' => 'Marketing banner deleted.'], 200);
    }
}
