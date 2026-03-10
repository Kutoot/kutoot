<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanTier extends Model
{
    /** @use HasFactory<\Database\Factories\LoanTierFactory> */
    use HasFactory;

    protected $fillable = [
        'min_streak_months',
        'max_loan_amount',
        'interest_rate_percentage',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_loan_amount' => 'decimal:2',
            'interest_rate_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<MerchantLocationLoan, $this>
     */
    public function loans(): HasMany
    {
        return $this->hasMany(MerchantLocationLoan::class);
    }
}
