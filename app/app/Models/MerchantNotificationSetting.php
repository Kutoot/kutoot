<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantNotificationSetting extends Model
{
    protected $fillable = [
        'merchant_location_id',
        'enabled',
        'channels',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'channels' => 'array',
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
     * Get default channel settings.
     *
     * @return array<string, bool>
     */
    public static function defaultChannels(): array
    {
        return [
            'email' => true,
            'sms' => true,
            'whatsapp' => true,
        ];
    }
}
