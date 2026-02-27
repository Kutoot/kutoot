<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Enums\CreatorType;
use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class RealDataSeeder extends Seeder
{
    /**
     * Seed real campaigns and subscription plans with their bindings.
     *
     * Campaigns: Tata Sierra, iPhone, Villa, Jewellery, BMW Bike
     * Plans: FREE, Basic, VIP, VIP+, Pro, Global -- each with progressive campaign access.
     */
    public function run(): void
    {
        $admin = User::first();

        if (! $admin) {
            $this->command?->warn('Skipping RealDataSeeder: no admin user found.');

            return;
        }

        // -- Categories --
        $categories = [
            'automotive' => CampaignCategory::firstOrCreate(
                ['slug' => 'automotive'],
                ['name' => 'Automotive'],
            ),
            'electronics' => CampaignCategory::firstOrCreate(
                ['slug' => 'electronics'],
                ['name' => 'Electronics'],
            ),
            'real-estate' => CampaignCategory::firstOrCreate(
                ['slug' => 'real-estate'],
                ['name' => 'Real Estate'],
            ),
            'lifestyle' => CampaignCategory::firstOrCreate(
                ['slug' => 'lifestyle'],
                ['name' => 'Lifestyle'],
            ),
        ];

        // -- Campaigns --

        /** @var array<string, array{reward_name: string, description: string, code: string, category: string, stamp_target: int, reward_cost_target: float, stamp_slots: int, stamp_slot_min: int, stamp_slot_max: int, marketing_bounty_percentage: int, is_premium: bool}> */
        $campaignDefs = [
            'tata_sierra' => [
                'reward_name' => 'Tata Sierra',
                'description' => 'Win the all-new Tata Sierra SUV -- rugged, stylish, and built for adventure.',
                'code' => 'SIERRA',
                'category' => 'automotive',
                'stamp_target' => 50,
                'reward_cost_target' => 1500000.00,
                'stamp_slots' => 6,
                'stamp_slot_min' => 1,
                'stamp_slot_max' => 50,
                'marketing_bounty_percentage' => 40,
                'is_premium' => true,
            ],
            'iphone' => [
                'reward_name' => 'iPhone',
                'description' => 'Win the latest Apple iPhone -- power, elegance, and innovation in your hands.',
                'code' => 'IPHONE',
                'category' => 'electronics',
                'stamp_target' => 20,
                'reward_cost_target' => 120000.00,
                'stamp_slots' => 5,
                'stamp_slot_min' => 1,
                'stamp_slot_max' => 30,
                'marketing_bounty_percentage' => 25,
                'is_premium' => false,
            ],
            'villa' => [
                'reward_name' => 'Villa',
                'description' => 'Win a luxury villa -- your dream home awaits with premium interiors and stunning views.',
                'code' => 'VILLA',
                'category' => 'real-estate',
                'stamp_target' => 100,
                'reward_cost_target' => 5000000.00,
                'stamp_slots' => 6,
                'stamp_slot_min' => 1,
                'stamp_slot_max' => 99,
                'marketing_bounty_percentage' => 45,
                'is_premium' => true,
            ],
            'jewellery' => [
                'reward_name' => 'Jewellery',
                'description' => 'Win exquisite gold and diamond jewellery -- timeless beauty, crafted to perfection.',
                'code' => 'JEWEL',
                'category' => 'lifestyle',
                'stamp_target' => 25,
                'reward_cost_target' => 200000.00,
                'stamp_slots' => 5,
                'stamp_slot_min' => 1,
                'stamp_slot_max' => 40,
                'marketing_bounty_percentage' => 30,
                'is_premium' => false,
            ],
            'bmw_bike' => [
                'reward_name' => 'BMW Bike',
                'description' => 'Win a BMW motorcycle -- precision engineering and pure riding thrill.',
                'code' => 'BMWBIK',
                'category' => 'automotive',
                'stamp_target' => 35,
                'reward_cost_target' => 500000.00,
                'stamp_slots' => 6,
                'stamp_slot_min' => 1,
                'stamp_slot_max' => 45,
                'marketing_bounty_percentage' => 35,
                'is_premium' => true,
            ],
        ];

        $campaigns = [];
        foreach ($campaignDefs as $key => $def) {
            $campaigns[$key] = Campaign::create([
                'category_id' => $categories[$def['category']]->id,
                'creator_type' => CreatorType::Admin,
                'creator_id' => $admin->id,
                'reward_name' => $def['reward_name'],
                'description' => $def['description'],
                'code' => $def['code'],
                'status' => CampaignStatus::Active,
                'start_date' => now(),
                'reward_cost_target' => $def['reward_cost_target'],
                'stamp_target' => $def['stamp_target'],
                'stamp_slots' => $def['stamp_slots'],
                'stamp_slot_min' => $def['stamp_slot_min'],
                'stamp_slot_max' => $def['stamp_slot_max'],
                'stamp_editable_on_plan_purchase' => true,
                'stamp_editable_on_coupon_redemption' => false,
                'marketing_bounty_percentage' => $def['marketing_bounty_percentage'],
                'collected_commission_cache' => 0,
                'issued_stamps_cache' => 0,
                'is_active' => true,
                'is_premium' => $def['is_premium'],
            ]);
        }

        // -- Subscription Plans and Campaign Bindings --

        /** @var array<int, array{name: string, price: float, sort_order: int, stamps_on_purchase: int, stamp_denomination: float, stamps_per_denomination: int, max_discounted_bills: int, max_redeemable_amount: float, duration_days: int|null, is_default: bool, best_value: bool, campaign_keys: list<string>}> */
        $planDefs = [
            [
                'name' => 'FREE',
                'price' => 0,
                'sort_order' => 1,
                'stamps_on_purchase' => 0,
                'stamp_denomination' => 100,
                'stamps_per_denomination' => 1,
                'max_discounted_bills' => 3,
                'max_redeemable_amount' => 300,
                'duration_days' => null,
                'is_default' => true,
                'best_value' => false,
                'campaign_keys' => ['iphone'],
            ],
            [
                'name' => 'Basic',
                'price' => 99,
                'sort_order' => 2,
                'stamps_on_purchase' => 2,
                'stamp_denomination' => 100,
                'stamps_per_denomination' => 1,
                'max_discounted_bills' => 5,
                'max_redeemable_amount' => 500,
                'duration_days' => 30,
                'is_default' => false,
                'best_value' => false,
                'campaign_keys' => ['iphone', 'jewellery'],
            ],
            [
                'name' => 'VIP',
                'price' => 299,
                'sort_order' => 3,
                'stamps_on_purchase' => 5,
                'stamp_denomination' => 50,
                'stamps_per_denomination' => 2,
                'max_discounted_bills' => 15,
                'max_redeemable_amount' => 1500,
                'duration_days' => 60,
                'is_default' => false,
                'best_value' => false,
                'campaign_keys' => ['iphone', 'jewellery', 'bmw_bike'],
            ],
            [
                'name' => 'VIP+',
                'price' => 599,
                'sort_order' => 4,
                'stamps_on_purchase' => 10,
                'stamp_denomination' => 25,
                'stamps_per_denomination' => 3,
                'max_discounted_bills' => 30,
                'max_redeemable_amount' => 3000,
                'duration_days' => 90,
                'is_default' => false,
                'best_value' => true,
                'campaign_keys' => ['iphone', 'jewellery', 'bmw_bike', 'tata_sierra'],
            ],
            [
                'name' => 'Pro',
                'price' => 999,
                'sort_order' => 5,
                'stamps_on_purchase' => 15,
                'stamp_denomination' => 15,
                'stamps_per_denomination' => 5,
                'max_discounted_bills' => 50,
                'max_redeemable_amount' => 5000,
                'duration_days' => 180,
                'is_default' => false,
                'best_value' => false,
                'campaign_keys' => ['iphone', 'jewellery', 'bmw_bike', 'tata_sierra', 'villa'],
            ],
            [
                'name' => 'Global',
                'price' => 1999,
                'sort_order' => 6,
                'stamps_on_purchase' => 25,
                'stamp_denomination' => 10,
                'stamps_per_denomination' => 8,
                'max_discounted_bills' => 100,
                'max_redeemable_amount' => 10000,
                'duration_days' => 365,
                'is_default' => false,
                'best_value' => false,
                'campaign_keys' => array_keys($campaignDefs),
            ],
        ];

        foreach ($planDefs as $def) {
            $campaignKeys = $def['campaign_keys'];
            unset($def['campaign_keys']);

            $plan = SubscriptionPlan::updateOrCreate(
                ['name' => $def['name']],
                $def,
            );

            $campaignIds = collect($campaignKeys)
                ->map(fn (string $key): int => $campaigns[$key]->id)
                ->all();

            $plan->campaigns()->sync($campaignIds);
        }

        $this->command?->info('Seeded 5 real campaigns and 6 subscription plans with bindings.');
    }
}
