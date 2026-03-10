<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreBannerResource;
use App\Models\StoreBanner;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * @tags Store Banners
 */
class StoreBannerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $banners = Cache::remember('store-banners:active', 300, function () {
            return StoreBanner::query()
                ->with('media')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->get();
        });

        return StoreBannerResource::collection($banners);
    }
}
