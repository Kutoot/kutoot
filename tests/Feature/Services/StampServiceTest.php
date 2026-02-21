<?php

use App\Enums\StampSource;
use App\Models\Campaign;
use App\Models\Stamp;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\StampService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(StampService::class);
});

test('it generates stamp codes in campaign format when stamp config is set', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'code' => 'DIWALI2026',
        'stamp_slots' => 6,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 49,
        'stamp_editable_on_plan_purchase' => false,
        'stamp_editable_on_coupon_redemption' => false,
    ]);
    $user->update(['primary_campaign_id' => $campaign->id]);

    $plan = SubscriptionPlan::factory()->create([
        'stamps_on_purchase' => 2,
    ]);

    $this->service->awardStampsForPlanPurchase($user, $plan);

    $stamps = Stamp::where('campaign_id', $campaign->id)->get();
    expect($stamps)->toHaveCount(2);

    foreach ($stamps as $stamp) {
        expect($stamp->code)->toStartWith('DIWALI2026-');
        // Should have 6 slots separated by dashes (7 parts total with prefix)
        $parts = explode('-', $stamp->code);
        expect($parts)->toHaveCount(7); // DIWALI2026 + 6 slots
    }
});

test('it generates legacy format when campaign has no stamp config', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'code' => null,
        'stamp_slots' => null,
        'stamp_slot_min' => null,
        'stamp_slot_max' => null,
    ]);
    $user->update(['primary_campaign_id' => $campaign->id]);

    $plan = SubscriptionPlan::factory()->create([
        'stamps_on_purchase' => 1,
    ]);

    $this->service->awardStampsForPlanPurchase($user, $plan);

    $stamp = Stamp::where('campaign_id', $campaign->id)->first();
    expect($stamp->code)->toStartWith('STP-');
});

test('stamp slot values are strictly ascending', function () {
    $campaign = Campaign::factory()->create([
        'code' => 'TEST',
        'stamp_slots' => 6,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 49,
    ]);

    for ($i = 0; $i < 20; $i++) {
        $values = $this->service->generateStampSlotValues($campaign);
        expect($values)->toHaveCount(6);

        for ($j = 1; $j < count($values); $j++) {
            expect($values[$j])->toBeGreaterThan($values[$j - 1]);
        }

        foreach ($values as $v) {
            expect($v)->toBeGreaterThanOrEqual(1);
            expect($v)->toBeLessThanOrEqual(49);
        }
    }
});

test('stamp slot values stay within configured range', function () {
    $campaign = Campaign::factory()->create([
        'code' => 'RANGE',
        'stamp_slots' => 3,
        'stamp_slot_min' => 10,
        'stamp_slot_max' => 20,
    ]);

    for ($i = 0; $i < 20; $i++) {
        $values = $this->service->generateStampSlotValues($campaign);
        expect($values)->toHaveCount(3);

        foreach ($values as $v) {
            expect($v)->toBeGreaterThanOrEqual(10);
            expect($v)->toBeLessThanOrEqual(20);
        }
    }
});

test('campaign getPossibleCombinations returns correct binomial', function () {
    $campaign = Campaign::factory()->create([
        'code' => 'COMBO',
        'stamp_slots' => 6,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 49,
    ]);

    // C(49, 6) = 13,983,816
    expect($campaign->getPossibleCombinations())->toBe(13983816);
});

test('campaign getPossibleCombinations returns 0 when stamp config is missing', function () {
    $campaign = Campaign::factory()->create([
        'code' => null,
        'stamp_slots' => null,
        'stamp_slot_min' => null,
        'stamp_slot_max' => null,
    ]);

    expect($campaign->getPossibleCombinations())->toBe(0);
});

test('campaign formatStampCode zero-pads correctly', function () {
    $campaign = Campaign::factory()->create([
        'code' => 'TEST',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 99,
    ]);

    $code = $campaign->formatStampCode([3, 12, 45]);
    expect($code)->toBe('TEST-03-12-45');
});

test('campaign formatStampCode handles single digit max', function () {
    $campaign = Campaign::factory()->create([
        'code' => 'MINI',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 9,
    ]);

    $code = $campaign->formatStampCode([2, 5, 8]);
    expect($code)->toBe('MINI-2-5-8');
});

test('stamps are editable when source matches editable flag', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'code' => 'EDIT',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
        'stamp_editable_on_plan_purchase' => true,
        'stamp_editable_on_coupon_redemption' => false,
    ]);
    $user->update(['primary_campaign_id' => $campaign->id]);

    $plan = SubscriptionPlan::factory()->create([
        'stamps_on_purchase' => 1,
    ]);

    $this->service->awardStampsForPlanPurchase($user, $plan);

    $stamp = Stamp::where('campaign_id', $campaign->id)->first();
    expect($stamp->editable_until)->not->toBeNull();
    expect($stamp->isEditable())->toBeTrue();
});

test('stamps are not editable when source does not match editable flag', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'code' => 'NOEDIT',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
        'stamp_editable_on_plan_purchase' => false,
        'stamp_editable_on_coupon_redemption' => true,
    ]);
    $user->update(['primary_campaign_id' => $campaign->id]);

    $plan = SubscriptionPlan::factory()->create([
        'stamps_on_purchase' => 1,
    ]);

    $this->service->awardStampsForPlanPurchase($user, $plan);

    $stamp = Stamp::where('campaign_id', $campaign->id)->first();
    expect($stamp->editable_until)->toBeNull();
    expect($stamp->isEditable())->toBeFalse();
});

test('bill payment stamps are never editable', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'code' => 'BILL',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
        'stamp_editable_on_plan_purchase' => true,
        'stamp_editable_on_coupon_redemption' => true,
    ]);
    $user->update(['primary_campaign_id' => $campaign->id]);

    $plan = SubscriptionPlan::factory()->create([
        'stamps_per_100' => 1,
    ]);
    UserSubscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'original_bill_amount' => 200,
        'amount' => 200,
    ]);

    $this->service->awardStampsForBill($transaction);

    $stamp = Stamp::where('campaign_id', $campaign->id)->first();
    expect($stamp->editable_until)->toBeNull();
});

test('updateStampCode updates stamp with valid slot values', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'code' => 'UPDATE',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
    ]);

    $stamp = Stamp::factory()->create([
        'user_id' => $user->id,
        'campaign_id' => $campaign->id,
        'code' => 'UPDATE-01-05-10',
        'editable_until' => now()->addMinutes(15),
    ]);

    $updated = $this->service->updateStampCode($stamp, [3, 12, 18]);
    expect($updated->code)->toBe('UPDATE-03-12-18');
});

test('updateStampCode fails when edit window has expired', function () {
    $campaign = Campaign::factory()->create([
        'code' => 'EXPIRED',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
    ]);

    $stamp = Stamp::factory()->create([
        'campaign_id' => $campaign->id,
        'code' => 'EXPIRED-01-05-10',
        'editable_until' => now()->subMinutes(1),
    ]);

    expect(fn () => $this->service->updateStampCode($stamp, [3, 12, 18]))
        ->toThrow(InvalidArgumentException::class, 'expired');
});

test('updateStampCode fails with non-ascending slot values', function () {
    $campaign = Campaign::factory()->create([
        'code' => 'ASC',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
    ]);

    $stamp = Stamp::factory()->create([
        'campaign_id' => $campaign->id,
        'code' => 'ASC-01-05-10',
        'editable_until' => now()->addMinutes(15),
    ]);

    expect(fn () => $this->service->updateStampCode($stamp, [10, 5, 3]))
        ->toThrow(InvalidArgumentException::class, 'ascending');
});

test('updateStampCode fails with out-of-range slot values', function () {
    $campaign = Campaign::factory()->create([
        'code' => 'OOR',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
    ]);

    $stamp = Stamp::factory()->create([
        'campaign_id' => $campaign->id,
        'code' => 'OOR-01-05-10',
        'editable_until' => now()->addMinutes(15),
    ]);

    expect(fn () => $this->service->updateStampCode($stamp, [1, 10, 25]))
        ->toThrow(InvalidArgumentException::class, 'out of range');
});

test('updateStampCode fails with duplicate code in same campaign', function () {
    $campaign = Campaign::factory()->create([
        'code' => 'DUP',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
    ]);

    Stamp::factory()->create([
        'campaign_id' => $campaign->id,
        'code' => 'DUP-03-12-18',
    ]);

    $stamp = Stamp::factory()->create([
        'campaign_id' => $campaign->id,
        'code' => 'DUP-01-05-10',
        'editable_until' => now()->addMinutes(15),
    ]);

    expect(fn () => $this->service->updateStampCode($stamp, [3, 12, 18]))
        ->toThrow(InvalidArgumentException::class, 'already taken');
});

test('updateStampCode fails with wrong number of slots', function () {
    $campaign = Campaign::factory()->create([
        'code' => 'SLOTS',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
    ]);

    $stamp = Stamp::factory()->create([
        'campaign_id' => $campaign->id,
        'code' => 'SLOTS-01-05-10',
        'editable_until' => now()->addMinutes(15),
    ]);

    expect(fn () => $this->service->updateStampCode($stamp, [3, 12]))
        ->toThrow(InvalidArgumentException::class, 'Expected 3');
});

test('no duplicate stamp codes are generated within a campaign', function () {
    $campaign = Campaign::factory()->create([
        'code' => 'UNIQUE',
        'stamp_slots' => 2,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 5,
        // C(5,2) = 10 possible codes
    ]);

    $user = User::factory()->create();
    $user->update(['primary_campaign_id' => $campaign->id]);

    $plan = SubscriptionPlan::factory()->create([
        'stamps_on_purchase' => 8,
    ]);

    $this->service->awardStampsForPlanPurchase($user, $plan);

    $codes = Stamp::where('campaign_id', $campaign->id)->pluck('code')->toArray();
    expect($codes)->toHaveCount(8);
    // All codes should be unique
    expect(array_unique($codes))->toHaveCount(8);
});

test('coupon redemption stamps use CouponRedemption source', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'code' => 'COUPON',
        'stamp_slots' => 3,
        'stamp_slot_min' => 1,
        'stamp_slot_max' => 20,
        'stamp_editable_on_coupon_redemption' => true,
    ]);
    $user->update(['primary_campaign_id' => $campaign->id]);

    $plan = SubscriptionPlan::factory()->create([
        'stamps_per_100' => 1,
    ]);
    UserSubscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'original_bill_amount' => 300,
        'amount' => 300,
    ]);

    $count = $this->service->awardStampsForCouponRedemption($transaction);
    expect($count)->toBe(3);

    $stamps = Stamp::where('campaign_id', $campaign->id)->get();
    expect($stamps)->toHaveCount(3);

    foreach ($stamps as $stamp) {
        expect($stamp->source)->toBe(StampSource::CouponRedemption);
        expect($stamp->editable_until)->not->toBeNull();
        expect($stamp->code)->toStartWith('COUPON-');
    }
});
