<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Services\PublicBookingService;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class BookingController extends Controller
{
    public function ical(string $reference, PublicBookingService $bookingService): Response
    {
        $visit = Visit::query()
            ->where('booking_reference', $reference)
            ->with(['host', 'department', 'site', 'visitors'])
            ->firstOrFail();

        $icsContent = $bookingService->generateIcs($visit);
        $filename = 'appointment-' . $reference . '.ics';

        return response($icsContent, SymfonyResponse::HTTP_OK, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
