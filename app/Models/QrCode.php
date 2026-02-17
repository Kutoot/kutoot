<?php

namespace App\Models;

use App\Enums\QrCodeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_code',
        'token',
        'merchant_location_id',
        'status',
        'linked_at',
        'linked_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'linked_at' => 'datetime',
            'status' => QrCodeStatus::class,
        ];
    }

    public function merchantLocation(): BelongsTo
    {
        return $this->belongsTo(MerchantLocation::class);
    }

    public function executive(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_by');
    }

    public function getUrlAttribute(): string
    {
        return route('qr.scan', ['token' => $this->token]);
    }
}
