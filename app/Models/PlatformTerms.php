<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformTerms extends Model
{
    protected $fillable = [
        'version',
        'title',
        'content',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Ensure only one version is active at a time.
     */
    protected static function booted(): void
    {
        static::saving(function (PlatformTerms $terms) {
            if ($terms->is_active) {
                static::where('id', '!=', $terms->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }

    /**
     * Return the currently active platform terms model or null.
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Scope for active terms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
