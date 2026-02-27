<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarketingBannerResource;
use App\Models\MarketingBanner;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * @tags Marketing Banners
 */
class MarketingBannerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $banners = Cache::remember('marketing-banners:active', 300, function () {
            return MarketingBanner::query()
                ->with('media')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->get();
        });

        return MarketingBannerResource::collection($banners);
    }
}
