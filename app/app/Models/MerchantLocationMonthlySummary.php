<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantLocationMonthlySummary extends Model
{
    /** @use HasFactory<\Database\Factories\MerchantLocationMonthlySummaryFactory> */
    use HasFactory;

    protected $table = 'merchant_location_monthly_summaries';

    protected $fillable = [
        'merchant_location_id',
        'year',
        'month',
        'total_bill_amount',
        'total_commission_amount',
        'net_amount',
        'transaction_count',
        'target_met',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_bill_amount' => 'decimal:2',
            'total_commission_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'target_met' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<MerchantLocation, $this>
     */
    public function merchantLocation(): BelongsTo
    {
        return $this->belongsTo(MerchantLocation::class);
    }
}
