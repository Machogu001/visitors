<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Models;

use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory;

    public const DEFAULT_SLUG = 'default';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'address',
        'timezone',
        'is_active',
        'allow_general_booking',
        'general_booking_host_id',
        'allow_department_booking',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_general_booking' => 'boolean',
        'allow_department_booking' => 'boolean',
    ];

    public static function default(): self
    {
        return self::query()->firstOrCreate(
            ['slug' => self::DEFAULT_SLUG],
            [
                'name' => 'Default Site',
                'timezone' => 'Europe/Berlin',
                'is_active' => true,
            ],
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function generalBookingHost(): BelongsTo
    {
        return $this->belongsTo(User::class, 'general_booking_host_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function monitors(): HasMany
    {
        return $this->hasMany(Monitor::class);
    }

    public function recurringVisitSeries(): HasMany
    {
        return $this->hasMany(RecurringVisitSeries::class);
    }
}
