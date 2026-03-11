<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreStoreBannerRequest;
use App\Http\Requests\Api\V1\Admin\UpdateStoreBannerRequest;
use App\Http\Resources\StoreBannerResource;
use App\Models\StoreBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * @tags Admin / Store Banners
 */
class StoreBannerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', StoreBanner::class);

        $banners = StoreBanner::query()
            ->with('media')
            ->when($request->input('search'), fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->has('filter.is_active'), fn ($q) => $q->where('is_active', $request->boolean('filter.is_active')))
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return StoreBannerResource::collection($banners);
    }

    public function show(StoreBanner $storeBanner): StoreBannerResource
    {
        $this->authorize('view', $storeBanner);

        $storeBanner->load('media');

        return new StoreBannerResource($storeBanner);
    }

    public function store(StoreStoreBannerRequest $request): JsonResponse
    {
        $banner = StoreBanner::create($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $banner->addMediaFromRequest('image')->toMediaCollection('image');
        }

        $banner->load('media');

        Cache::forget('store-banners:active');

        return (new StoreBannerResource($banner))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateStoreBannerRequest $request, StoreBanner $storeBanner): StoreBannerResource
    {
        $storeBanner->update($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $storeBanner->addMediaFromRequest('image')->toMediaCollection('image');
        }

        $storeBanner->load('media');

        Cache::forget('store-banners:active');

        return new StoreBannerResource($storeBanner);
    }

    public function destroy(StoreBanner $storeBanner): JsonResponse
    {
        $this->authorize('delete', $storeBanner);

        $storeBanner->delete();

        Cache::forget('store-banners:active');

        return response()->json(['message' => 'Store banner deleted.'], 200);
    }
}
