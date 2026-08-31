<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'name',
        'description',
        'location',
        'head_user_id',
        'receptionist_user_id',
        'is_active',
        'allow_public_booking',
        'requires_approval',
        'is_finance_department',
        'has_dedicated_reception',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_public_booking' => 'boolean',
        'requires_approval' => 'boolean',
        'is_finance_department' => 'boolean',
        'has_dedicated_reception' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $department): void {
            if (blank($department->site_id)) {
                $department->site_id = Site::default()->id;
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function headUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function receptionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receptionist_user_id');
    }

    public function scopeBookable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('allow_public_booking', true);
    }

    /**
     * Relationships to table users for foreign key
     * EXAMPLE HOW TO ACCESS: Access all users in department
     *                         --> $users = $department->users()
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
