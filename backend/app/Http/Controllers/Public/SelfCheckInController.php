<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\Visitor;
use App\Services\VisitActionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SelfCheckInController extends Controller
{
    public function __invoke(Request $request, Visit $visit, Visitor $visitor, VisitActionService $actions): View|RedirectResponse
    {
        abort_unless(config('reception.self_check_in.enabled'), 404);
        abort_unless($visit->visitors()->whereKey($visitor->id)->exists(), 404);
        abort_unless($visit->status === 'planned', 410);

        $opensAt = $visit->scheduled_from->copy()->subHours(config('reception.self_check_in.window_hours'));
        $closesAt = $visit->scheduled_until->copy()->addHours(config('reception.self_check_in.grace_hours'));
        $insideWindow = now()->between($opensAt, $closesAt);

        if ($request->isMethod('post')) {
            abort_unless($insideWindow, 422, 'Self check-in is not available at this time.');
            $participant = $actions->checkInParticipant($visit, $visitor, null);

            return redirect()->to($request->fullUrl())->with('status',
                filled($participant->pivot->checked_in_at) ? __('You are checked in.') : __('Check-in could not be completed.')
            );
        }

        $participant = $visit->visitors()->whereKey($visitor->id)->firstOrFail();
        $visit->loadMissing(['site:id,name', 'host:id,first_name,name']);

        return view('public.self-check-in', compact('visit', 'participant', 'insideWindow'));
    }
}