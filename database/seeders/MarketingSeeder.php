<?php

namespace Database\Seeders;

use App\Models\FeaturedBanner;
use App\Models\MarketingBanner;
use App\Models\NewsArticle;
use App\Models\StoreBanner;
use Illuminate\Database\Seeder;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        MarketingBanner::factory()->count(6)->create();
        StoreBanner::factory()->count(6)->create();
        FeaturedBanner::factory()->count(4)->create();
        NewsArticle::factory()->count(4)->published()->create();
    }
}
