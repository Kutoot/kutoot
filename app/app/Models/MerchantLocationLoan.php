<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantLocationLoan extends Model
{
    /** @use HasFactory<\Database\Factories\MerchantLocationLoanFactory> */
    use HasFactory;

    protected $fillable = [
        'merchant_location_id',
        'loan_tier_id',
        'amount',
        'status',
        'streak_months_at_approval',
        'approved_at',
        'streak_broken_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => LoanStatus::class,
            'approved_at' => 'datetime',
            'streak_broken_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<MerchantLocation, $this>
     */
    public function merchantLocation(): BelongsTo
    {
        return $this->belongsTo(MerchantLocation::class);
    }

    /**
     * @return BelongsTo<LoanTier, $this>
     */
    public function loanTier(): BelongsTo
    {
        return $this->belongsTo(LoanTier::class);
    }
}
