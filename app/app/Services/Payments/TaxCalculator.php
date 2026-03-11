<?php

namespace App\Services\Payments;

use App\Enums\PlanTaxType;
use InvalidArgumentException;

class TaxCalculator
{
    /**
     * Calculate total for a plan purchase (all amounts in Paise — integer).
     *
     * @return array{base: int, gst: int, total: int}
     */
    public function calculatePlanTotal(int $priceInPaise, string $taxType): array
    {
        $type = PlanTaxType::from($taxType);

        return match ($type) {
            PlanTaxType::Inclusive => $this->calculateInclusive($priceInPaise),
            PlanTaxType::Exclusive => $this->calculateExclusive($priceInPaise),
            PlanTaxType::None => [
                'base' => $priceInPaise,
                'gst' => 0,
                'total' => $priceInPaise,
            ],
        };
    }

    /**
     * GST is included in the plan price. Back-calculate base.
     *
     * @return array{base: int, gst: int, total: int}
     */
    private function calculateInclusive(int $priceInPaise): array
    {
        $gstRate = (int) config('app.gst_rate', 18);
        $base = (int) floor($priceInPaise * 100 / (100 + $gstRate));
        $gst = $priceInPaise - $base;

        return [
            'base' => $base,
            'gst' => $gst,
            'total' => $priceInPaise,
        ];
    }

    /**
     * GST is added on top of the plan price.
     *
     * @return array{base: int, gst: int, total: int}
     */
    private function calculateExclusive(int $priceInPaise): array
    {
        $gstRate = (int) config('app.gst_rate', 18);
        $gst = (int) floor($priceInPaise * $gstRate / 100);
        $total = $priceInPaise + $gst;

        return [
            'base' => $priceInPaise,
            'gst' => $gst,
            'total' => $total,
        ];
    }

    /**
     * Calculate store purchase split (all amounts in Paise — integer).
     *
     * @return array{discounted_bill: int, platform_fee: int, gst_on_fee: int, store_share: int, kutoot_share: int}
     */
    public function calculateStoreSplit(
        int $billAmountInPaise,
        int $discountInPaise,
        float $platformFeePercent,
        bool $platformFeeIsFixed,
        int $fixedFeeInPaise = 0,
    ): array {
        $discountedBill = $billAmountInPaise - $discountInPaise;

        if ($discountedBill < 0) {
            throw new InvalidArgumentException('Discount cannot exceed bill amount.');
        }

        // Calculate platform fee
        if ($platformFeeIsFixed) {
            $platformFee = $fixedFeeInPaise;
        } else {
            $platformFee = (int) floor($discountedBill * $platformFeePercent / 100);
        }

        // GST on platform fee (18%)
        $gstRate = (int) config('app.gst_rate', 18);
        $gstOnFee = (int) floor($platformFee * $gstRate / 100);

        $kutootShare = $platformFee + $gstOnFee;
        $storeShare = $discountedBill - $kutootShare;

        // Validate: discount must not reduce bill below platform fee + GST
        if ($storeShare < 0) {
            throw new InvalidArgumentException(
                'Discount reduces bill below minimum required for platform fee + GST. '
                ."Discounted bill: {$discountedBill}, Platform fee + GST: {$kutootShare}"
            );
        }

        return [
            'discounted_bill' => $discountedBill,
            'platform_fee' => $platformFee,
            'gst_on_fee' => $gstOnFee,
            'store_share' => $storeShare,
            'kutoot_share' => $kutootShare,
        ];
    }
}
