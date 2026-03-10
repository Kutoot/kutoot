<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Enums\TargetType;
use App\Models\LoanTier;
use App\Models\MerchantLocation;
use App\Models\MerchantLocationLoan;
use App\Models\MerchantLocationMonthlySummary;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class MonthlyTargetService
{
    /**
     * Recalculate the monthly summary for a given merchant location and calendar month.
     * Uses completed transactions only.
     */
    public function recalculateMonth(MerchantLocation $location, int $year, int $month): MerchantLocationMonthlySummary
    {
        $query = Transaction::query()
            ->where('merchant_location_id', $location->id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereIn('payment_status', [PaymentStatus::Completed, PaymentStatus::Paid]);

        $totalBillAmount = (float) $query->sum('original_bill_amount');
        $totalCommissionAmount = (float) $query->sum('commission_amount');
        $netAmount = $totalBillAmount - $totalCommissionAmount;
        $transactionCount = $query->count();

        $targetMet = $this->isTargetMet($location, $totalBillAmount, $totalCommissionAmount, $netAmount, $transactionCount);

        return MerchantLocationMonthlySummary::updateOrCreate(
            [
                'merchant_location_id' => $location->id,
                'year' => $year,
                'month' => $month,
            ],
            [
                'total_bill_amount' => $totalBillAmount,
                'total_commission_amount' => $totalCommissionAmount,
                'net_amount' => $netAmount,
                'transaction_count' => $transactionCount,
                'target_met' => $targetMet,
            ]
        );
    }

    /**
     * Determine if the monthly target is met for a location.
     */
    protected function isTargetMet(
        MerchantLocation $location,
        float $totalBillAmount,
        float $totalCommissionAmount,
        float $netAmount,
        int $transactionCount,
    ): bool {
        if (! $location->hasMonthlyTarget()) {
            return false;
        }

        $targetValue = (float) $location->monthly_target_value;

        return match ($location->monthly_target_type) {
            TargetType::Amount => $location->deduct_commission_from_target
                ? $netAmount >= $targetValue
                : $totalBillAmount >= $targetValue,
            TargetType::TransactionCount => $transactionCount >= (int) $targetValue,
            default => false,
        };
    }

    /**
     * Get the consecutive streak length for a merchant location.
     * Counts backward from the previous calendar month.
     */
    public function getStreakLength(MerchantLocation $location): int
    {
        return $location->getCurrentStreak();
    }

    /**
     * Check if a merchant location is eligible for a new loan.
     * Requires: streak >= 3, no active loan, and location participates in the program.
     */
    public function isEligibleForLoan(MerchantLocation $location): bool
    {
        if (! $location->hasMonthlyTarget()) {
            return false;
        }

        $streak = $this->getStreakLength($location);

        if ($streak < 3) {
            return false;
        }

        // Check no active loan exists
        $hasActiveLoan = MerchantLocationLoan::query()
            ->where('merchant_location_id', $location->id)
            ->where('status', LoanStatus::Active)
            ->exists();

        if ($hasActiveLoan) {
            return false;
        }

        // Check a matching tier exists
        return $this->getEligibleLoanTier($location) !== null;
    }

    /**
     * Get the highest eligible loan tier based on the current streak.
     */
    public function getEligibleLoanTier(MerchantLocation $location): ?LoanTier
    {
        $streak = $this->getStreakLength($location);

        return LoanTier::query()
            ->where('is_active', true)
            ->where('min_streak_months', '<=', $streak)
            ->orderByDesc('min_streak_months')
            ->first();
    }

    /**
     * Handle streak break for a merchant location.
     * Marks eligibility as paused but does NOT revoke existing active loans.
     * Records streak_broken_at on active loans for audit.
     */
    public function handleStreakBreak(MerchantLocation $location): void
    {
        MerchantLocationLoan::query()
            ->where('merchant_location_id', $location->id)
            ->where('status', LoanStatus::Active)
            ->whereNull('streak_broken_at')
            ->update(['streak_broken_at' => now()]);
    }

    /**
     * Process all active merchant locations for a given month.
     * Creates summary records and handles streak breaks.
     */
    public function processMonthForAllLocations(int $year, int $month): int
    {
        $processed = 0;

        MerchantLocation::query()
            ->where('is_active', true)
            ->whereNotNull('monthly_target_type')
            ->whereNotNull('monthly_target_value')
            ->chunkById(100, function ($locations) use ($year, $month, &$processed) {
                foreach ($locations as $location) {
                    DB::transaction(function () use ($location, $year, $month) {
                        $summary = $this->recalculateMonth($location, $year, $month);

                        if (! $summary->target_met) {
                            $this->handleStreakBreak($location);
                        }
                    });
                    $processed++;
                }
            });

        return $processed;
    }

    /**
     * Incrementally update the current month's summary when a new transaction is completed.
     * This avoids full recalculation on every transaction.
     */
    public function incrementCurrentMonthSummary(Transaction $transaction): void
    {
        if (! $transaction->merchant_location_id) {
            return;
        }

        $location = $transaction->merchantLocation;

        if (! $location || ! $location->hasMonthlyTarget()) {
            return;
        }

        $year = (int) $transaction->created_at->format('Y');
        $month = (int) $transaction->created_at->format('m');

        // Recalculate the full month to ensure accuracy
        $this->recalculateMonth($location, $year, $month);
    }
}
