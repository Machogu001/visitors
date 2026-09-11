<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandingSetting extends Model
{
    protected $fillable = [
        'logo_light_path',
        'logo_dark_path',
        'favicon_path',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate([]);
    }
}
