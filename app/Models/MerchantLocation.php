<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MerchantLocation extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\MerchantLocationFactory> */
    use HasFactory, InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'merchant_id',
        'branch_name',
        'commission_percentage',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'commission_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "Store location \"{$this->branch_name}\" was {$eventName}");
    }

    /**
     * @return BelongsTo<Merchant, $this>
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    /**
     * @return HasMany<DiscountCoupon, $this>
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(DiscountCoupon::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('media')
            ->acceptsMimeTypes([
                'image/jpeg', 'image/png', 'image/webp', 'image/gif',
                'video/mp4', 'video/webm', 'video/quicktime',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->format('webp')
            ->quality(80)
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->fit(Fit::Contain, 800, 600)
            ->format('webp')
            ->quality(85)
            ->withResponsiveImages();
    }
}
