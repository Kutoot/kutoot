<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed world data (countries, states, cities) if not already present
        if (\Nnjeim\World\Models\Country::query()->count() === 0) {
            $this->call(WorldSeeder::class);
        }

        $this->call([
            RolesAndPermissionsSeeder::class,
            BasePlanSeeder::class,
            SuperAdminSeeder::class,
            DummyDataSeeder::class,
            CampaignSeeder::class,
            QrCodeSeeder::class,
            MarketingSeeder::class,
            StoreDataSeeder::class,
        ]);
    }
}
