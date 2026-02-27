<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeaturedBannerResource;
use App\Models\FeaturedBanner;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * @tags Featured Banners
 */
class FeaturedBannerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $banners = Cache::remember('featured-banners:active', 300, function () {
            return FeaturedBanner::query()
                ->with('media')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->get();
        });

        return FeaturedBannerResource::collection($banners);
    }
}
