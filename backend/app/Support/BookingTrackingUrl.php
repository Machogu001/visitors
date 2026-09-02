<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Support;

use App\Models\Visit;
use Illuminate\Support\Facades\URL;

class BookingTrackingUrl
{
    /**
     * Generate a signed booking tracking link that expires once the visit's
     * scheduled end time passes. The controller additionally treats the link
     * as expired once the visitor has been checked in, whichever comes first.
     */
    public static function generate(Visit $visit): string
    {
        return URL::temporarySignedRoute(
            'public.book.track',
            $visit->scheduled_until,
            ['reference' => $visit->booking_reference],
        );
    }
}
