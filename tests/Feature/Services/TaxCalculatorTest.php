<?php

use App\Services\Payments\TaxCalculator;

beforeEach(function () {
    config(['app.gst_rate' => 18]);
});

// --- Plan Tax: Exclusive ---

test('exclusive tax adds GST on top of plan price', function () {
    $calculator = new TaxCalculator;

    // ₹1000 = 100000 paise, GST 18% = 18000 paise
    $result = $calculator->calculatePlanTotal(100000, 'exclusive');

    expect($result['base'])->toBe(100000)
        ->and($result['gst'])->toBe(18000)
        ->and($result['total'])->toBe(118000);
});

test('exclusive tax with small amount', function () {
    $calculator = new TaxCalculator;

    // ₹99 = 9900 paise, GST = floor(9900 * 0.18) = 1782
    $result = $calculator->calculatePlanTotal(9900, 'exclusive');

    expect($result['base'])->toBe(9900)
        ->and($result['gst'])->toBe(1782)
        ->and($result['total'])->toBe(11682);
});

// --- Plan Tax: Inclusive ---

test('inclusive tax back-calculates base from total', function () {
    $calculator = new TaxCalculator;

    // ₹1180 = 118000 paise (includes GST)
    // Base = floor(118000 * 100 / 118) = 100000
    // GST = 118000 - 100000 = 18000
    $result = $calculator->calculatePlanTotal(118000, 'inclusive');

    expect($result['base'])->toBe(100000)
        ->and($result['gst'])->toBe(18000)
        ->and($result['total'])->toBe(118000);
});

test('inclusive tax with odd amount avoids rounding errors', function () {
    $calculator = new TaxCalculator;

    // ₹999 = 99900 paise
    // Base = floor(99900 * 100 / 118) = 84661
    // GST = 99900 - 84661 = 15239
    $result = $calculator->calculatePlanTotal(99900, 'inclusive');

    expect($result['base'])->toBe(84661)
        ->and($result['gst'])->toBe(15239)
        ->and($result['total'])->toBe(99900)
        ->and($result['base'] + $result['gst'])->toBe($result['total']);
});

// --- Plan Tax: None ---

test('no tax returns plan price as total', function () {
    $calculator = new TaxCalculator;

    $result = $calculator->calculatePlanTotal(100000, 'none');

    expect($result['base'])->toBe(100000)
        ->and($result['gst'])->toBe(0)
        ->and($result['total'])->toBe(100000);
});

// --- Store Split Calculations ---

test('store split calculates correct shares with fixed fee', function () {
    $calculator = new TaxCalculator;

    // Bill: ₹500 (50000 paise), Discount: ₹50 (5000 paise), Fixed fee: ₹10 (1000 paise)
    // Discounted bill = 45000
    // Platform fee = 1000
    // GST on fee = floor(1000 * 0.18) = 180
    // Kutoot share = 1000 + 180 = 1180
    // Store share = 45000 - 1180 = 43820
    $result = $calculator->calculateStoreSplit(
        billAmountInPaise: 50000,
        discountInPaise: 5000,
        platformFeePercent: 0,
        platformFeeIsFixed: true,
        fixedFeeInPaise: 1000,
    );

    expect($result['discounted_bill'])->toBe(45000)
        ->and($result['platform_fee'])->toBe(1000)
        ->and($result['gst_on_fee'])->toBe(180)
        ->and($result['kutoot_share'])->toBe(1180)
        ->and($result['store_share'])->toBe(43820)
        ->and($result['store_share'] + $result['kutoot_share'])->toBe($result['discounted_bill']);
});

test('store split calculates correct shares with percentage fee', function () {
    $calculator = new TaxCalculator;

    // Bill: ₹1000 (100000 paise), Discount: ₹0, Fee: 10%
    // Platform fee = floor(100000 * 10 / 100) = 10000
    // GST on fee = floor(10000 * 0.18) = 1800
    // Kutoot share = 10000 + 1800 = 11800
    // Store share = 100000 - 11800 = 88200
    $result = $calculator->calculateStoreSplit(
        billAmountInPaise: 100000,
        discountInPaise: 0,
        platformFeePercent: 10,
        platformFeeIsFixed: false,
    );

    expect($result['discounted_bill'])->toBe(100000)
        ->and($result['platform_fee'])->toBe(10000)
        ->and($result['gst_on_fee'])->toBe(1800)
        ->and($result['kutoot_share'])->toBe(11800)
        ->and($result['store_share'])->toBe(88200);
});

test('store split throws when discount exceeds bill', function () {
    $calculator = new TaxCalculator;

    $calculator->calculateStoreSplit(
        billAmountInPaise: 5000,
        discountInPaise: 6000,
        platformFeePercent: 0,
        platformFeeIsFixed: true,
        fixedFeeInPaise: 1000,
    );
})->throws(InvalidArgumentException::class, 'Discount cannot exceed bill amount');

test('store split throws when discount reduces bill below platform fee', function () {
    $calculator = new TaxCalculator;

    // Bill: ₹20 (2000 paise), Discount: ₹15 (1500 paise), Fixed fee: ₹10 (1000 paise)
    // Discounted bill = 500
    // Platform fee = 1000
    // GST on fee = 180
    // Kutoot share = 1180 > 500 → store share would be negative
    $calculator->calculateStoreSplit(
        billAmountInPaise: 2000,
        discountInPaise: 1500,
        platformFeePercent: 0,
        platformFeeIsFixed: true,
        fixedFeeInPaise: 1000,
    );
})->throws(InvalidArgumentException::class, 'Discount reduces bill below minimum');

test('all calculations use integer paise (no floating point)', function () {
    $calculator = new TaxCalculator;

    // Test with amounts that would cause floating point issues
    $planResult = $calculator->calculatePlanTotal(33333, 'exclusive');
    expect($planResult['base'])->toBeInt()
        ->and($planResult['gst'])->toBeInt()
        ->and($planResult['total'])->toBeInt();

    $storeResult = $calculator->calculateStoreSplit(
        billAmountInPaise: 33333,
        discountInPaise: 1111,
        platformFeePercent: 7.5,
        platformFeeIsFixed: false,
    );
    expect($storeResult['discounted_bill'])->toBeInt()
        ->and($storeResult['platform_fee'])->toBeInt()
        ->and($storeResult['gst_on_fee'])->toBeInt()
        ->and($storeResult['store_share'])->toBeInt()
        ->and($storeResult['kutoot_share'])->toBeInt();
});
