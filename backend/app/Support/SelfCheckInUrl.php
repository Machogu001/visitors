<?php

namespace App\Support;

use App\Models\Visit;
use App\Models\Visitor;
use Illuminate\Support\Facades\URL;

final class SelfCheckInUrl
{
    public static function generate(Visit $visit, Visitor $visitor): ?string
    {
        if (! config('reception.self_check_in.enabled')) {
            return null;
        }

        $expiresAt = $visit->scheduled_until
            ->copy()
            ->addHours(config('reception.self_check_in.grace_hours'));

        return URL::temporarySignedRoute('public.self-check-in', $expiresAt, [
            'visit' => $visit->id,
            'visitor' => $visitor->id,
        ]);
    }
}