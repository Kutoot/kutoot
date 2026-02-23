<?php

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Enums\TargetType;
use App\Models\LoanTier;
use App\Models\MerchantLocation;
use App\Models\MerchantLocationLoan;
use App\Models\MerchantLocationMonthlySummary;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MonthlyTargetService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(MonthlyTargetService::class);
});

// ---- Monthly Summary Recalculation ----

test('it recalculates monthly summary with correct totals', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 50000,
        'deduct_commission_from_target' => true,
    ]);

    $user = User::factory()->create();

    // Create completed transactions for Jan 2026
    Transaction::factory()->count(3)->create([
        'user_id' => $user->id,
        'merchant_location_id' => $location->id,
        'original_bill_amount' => 20000,
        'commission_amount' => 1000,
        'payment_status' => PaymentStatus::Completed,
        'created_at' => '2026-01-15 10:00:00',
    ]);

    $summary = $this->service->recalculateMonth($location, 2026, 1);

    expect($summary->total_bill_amount)->toBe('60000.00');
    expect($summary->total_commission_amount)->toBe('3000.00');
    expect($summary->net_amount)->toBe('57000.00');
    expect($summary->transaction_count)->toBe(3);
    expect($summary->target_met)->toBeTrue(); // 57000 >= 50000
});

test('it excludes refunded and failed transactions from summary', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
        'deduct_commission_from_target' => false,
    ]);

    $user = User::factory()->create();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'merchant_location_id' => $location->id,
        'original_bill_amount' => 15000,
        'commission_amount' => 500,
        'payment_status' => PaymentStatus::Completed,
        'created_at' => '2026-01-10 10:00:00',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'merchant_location_id' => $location->id,
        'original_bill_amount' => 20000,
        'commission_amount' => 800,
        'payment_status' => PaymentStatus::Refunded,
        'created_at' => '2026-01-15 10:00:00',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'merchant_location_id' => $location->id,
        'original_bill_amount' => 5000,
        'commission_amount' => 200,
        'payment_status' => PaymentStatus::Failed,
        'created_at' => '2026-01-20 10:00:00',
    ]);

    $summary = $this->service->recalculateMonth($location, 2026, 1);

    expect($summary->total_bill_amount)->toBe('15000.00');
    expect($summary->transaction_count)->toBe(1);
    expect($summary->target_met)->toBeTrue(); // 15000 >= 10000 (no commission deduction)
});

test('target not met when amount is below threshold', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 100000,
        'deduct_commission_from_target' => true,
    ]);

    $user = User::factory()->create();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'merchant_location_id' => $location->id,
        'original_bill_amount' => 50000,
        'commission_amount' => 5000,
        'payment_status' => PaymentStatus::Completed,
        'created_at' => '2026-01-15 10:00:00',
    ]);

    $summary = $this->service->recalculateMonth($location, 2026, 1);

    expect($summary->target_met)->toBeFalse(); // 45000 < 100000
});

test('transaction count target type works correctly', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::TransactionCount,
        'monthly_target_value' => 5,
    ]);

    $user = User::factory()->create();

    Transaction::factory()->count(6)->create([
        'user_id' => $user->id,
        'merchant_location_id' => $location->id,
        'original_bill_amount' => 100,
        'commission_amount' => 5,
        'payment_status' => PaymentStatus::Completed,
        'created_at' => '2026-01-15 10:00:00',
    ]);

    $summary = $this->service->recalculateMonth($location, 2026, 1);

    expect($summary->transaction_count)->toBe(6);
    expect($summary->target_met)->toBeTrue(); // 6 >= 5
});

test('transaction count target not met when fewer transactions', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::TransactionCount,
        'monthly_target_value' => 10,
    ]);

    $user = User::factory()->create();

    Transaction::factory()->count(3)->create([
        'user_id' => $user->id,
        'merchant_location_id' => $location->id,
        'original_bill_amount' => 100,
        'commission_amount' => 5,
        'payment_status' => PaymentStatus::Completed,
        'created_at' => '2026-01-15 10:00:00',
    ]);

    $summary = $this->service->recalculateMonth($location, 2026, 1);

    expect($summary->target_met)->toBeFalse();
});

test('no target returns false for target_met', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => null,
        'monthly_target_value' => null,
    ]);

    $user = User::factory()->create();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'merchant_location_id' => $location->id,
        'original_bill_amount' => 50000,
        'payment_status' => PaymentStatus::Completed,
        'created_at' => '2026-01-15 10:00:00',
    ]);

    $summary = $this->service->recalculateMonth($location, 2026, 1);

    expect($summary->target_met)->toBeFalse();
});

test('empty month creates summary with zeros and target not met', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    $summary = $this->service->recalculateMonth($location, 2026, 1);

    expect($summary->total_bill_amount)->toBe('0.00');
    expect($summary->transaction_count)->toBe(0);
    expect($summary->target_met)->toBeFalse();
});

// ---- Streak Calculation ----

test('it calculates consecutive streak correctly', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    // Create 5 consecutive months of target met (Aug-Dec 2025) — current month is Feb 2026
    // Streak should count backward from Jan 2026
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2026,
        'month' => 1,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 12,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 11,
    ]);

    expect($this->service->getStreakLength($location))->toBe(3);
});

test('streak breaks when a month does not meet target', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2026,
        'month' => 1,
    ]);
    // December missed
    MerchantLocationMonthlySummary::factory()->targetNotMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 12,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 11,
    ]);

    expect($this->service->getStreakLength($location))->toBe(1);
});

test('streak is zero when no summaries exist', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    expect($this->service->getStreakLength($location))->toBe(0);
});

test('streak is zero when location has no target configured', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => null,
        'monthly_target_value' => null,
    ]);

    expect($this->service->getStreakLength($location))->toBe(0);
});

// ---- Loan Eligibility ----

test('location is eligible for loan with 3+ month streak and no active loan', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    LoanTier::factory()->create([
        'min_streak_months' => 3,
        'max_loan_amount' => 50000,
        'is_active' => true,
    ]);

    // Create 3-month streak
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2026,
        'month' => 1,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 12,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 11,
    ]);

    expect($this->service->isEligibleForLoan($location))->toBeTrue();
});

test('location is not eligible with streak less than 3', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    LoanTier::factory()->create([
        'min_streak_months' => 3,
        'is_active' => true,
    ]);

    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2026,
        'month' => 1,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 12,
    ]);

    expect($this->service->isEligibleForLoan($location))->toBeFalse();
});

test('location is not eligible with existing active loan', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    LoanTier::factory()->create([
        'min_streak_months' => 3,
        'is_active' => true,
    ]);

    MerchantLocationLoan::factory()->create([
        'merchant_location_id' => $location->id,
        'status' => LoanStatus::Active,
    ]);

    // Even with a 3-month streak, should not be eligible
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2026,
        'month' => 1,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 12,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 11,
    ]);

    expect($this->service->isEligibleForLoan($location))->toBeFalse();
});

test('location is eligible again after completing previous loan', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    LoanTier::factory()->create([
        'min_streak_months' => 3,
        'is_active' => true,
    ]);

    // Previous loan is completed
    MerchantLocationLoan::factory()->completed()->create([
        'merchant_location_id' => $location->id,
    ]);

    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2026,
        'month' => 1,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 12,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 11,
    ]);

    expect($this->service->isEligibleForLoan($location))->toBeTrue();
});

// ---- Loan Tier Resolution ----

test('it picks the highest applicable tier', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    $tier3 = LoanTier::factory()->create([
        'min_streak_months' => 3,
        'max_loan_amount' => 50000,
        'is_active' => true,
    ]);
    $tier6 = LoanTier::factory()->create([
        'min_streak_months' => 6,
        'max_loan_amount' => 100000,
        'is_active' => true,
    ]);
    LoanTier::factory()->create([
        'min_streak_months' => 12,
        'max_loan_amount' => 200000,
        'is_active' => true,
    ]);

    // 6-month streak
    for ($m = 1; $m <= 6; $m++) {
        $year = $m <= 1 ? 2026 : 2025;
        $month = $m <= 1 ? $m : 13 - (6 - $m);
        // Simpler: count backward from Jan 2026
        $date = now()->startOfMonth()->subMonths($m);
        MerchantLocationMonthlySummary::factory()->targetMet()->create([
            'merchant_location_id' => $location->id,
            'year' => (int) $date->format('Y'),
            'month' => (int) $date->format('m'),
        ]);
    }

    $tier = $this->service->getEligibleLoanTier($location);

    expect($tier)->not->toBeNull();
    expect($tier->id)->toBe($tier6->id);
    expect((float) $tier->max_loan_amount)->toBe(100000.00);
});

test('inactive tiers are excluded', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    LoanTier::factory()->create([
        'min_streak_months' => 3,
        'max_loan_amount' => 50000,
        'is_active' => false,
    ]);

    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2026,
        'month' => 1,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 12,
    ]);
    MerchantLocationMonthlySummary::factory()->targetMet()->create([
        'merchant_location_id' => $location->id,
        'year' => 2025,
        'month' => 11,
    ]);

    expect($this->service->getEligibleLoanTier($location))->toBeNull();
});

// ---- Streak Break Handling ----

test('streak break marks streak_broken_at on active loan', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    $loan = MerchantLocationLoan::factory()->create([
        'merchant_location_id' => $location->id,
        'status' => LoanStatus::Active,
        'streak_broken_at' => null,
    ]);

    $this->service->handleStreakBreak($location);

    $loan->refresh();
    expect($loan->streak_broken_at)->not->toBeNull();
    expect($loan->status)->toBe(LoanStatus::Active); // Loan stays active
});

test('streak break does not affect completed loans', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 10000,
    ]);

    $loan = MerchantLocationLoan::factory()->completed()->create([
        'merchant_location_id' => $location->id,
        'streak_broken_at' => null,
    ]);

    $this->service->handleStreakBreak($location);

    $loan->refresh();
    expect($loan->streak_broken_at)->toBeNull();
});

// ---- Commission Deduction Configuration ----

test('target met without commission deduction uses full bill amount', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 50000,
        'deduct_commission_from_target' => false,
    ]);

    $user = User::factory()->create();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'merchant_location_id' => $location->id,
        'original_bill_amount' => 55000,
        'commission_amount' => 10000,
        'payment_status' => PaymentStatus::Completed,
        'created_at' => '2026-01-15 10:00:00',
    ]);

    $summary = $this->service->recalculateMonth($location, 2026, 1);

    // Net = 55000 - 10000 = 45000 (would fail with deduction)
    // But without deduction, compare 55000 >= 50000
    expect($summary->target_met)->toBeTrue();
});

test('target not met with commission deduction when net amount is below target', function () {
    $location = MerchantLocation::factory()->create([
        'monthly_target_type' => TargetType::Amount,
        'monthly_target_value' => 50000,
        'deduct_commission_from_target' => true,
    ]);

    $user = User::factory()->create();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'merchant_location_id' => $location->id,
        'original_bill_amount' => 55000,
        'commission_amount' => 10000,
        'payment_status' => PaymentStatus::Completed,
        'created_at' => '2026-01-15 10:00:00',
    ]);

    $summary = $this->service->recalculateMonth($location, 2026, 1);

    // Net = 55000 - 10000 = 45000 < 50000
    expect($summary->target_met)->toBeFalse();
});
