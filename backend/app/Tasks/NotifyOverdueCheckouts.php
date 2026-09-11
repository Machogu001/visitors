<?php

namespace App\Tasks;

use App\Models\AuditEvent;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Notifications\Host\OverdueCheckoutNotification;
use App\Services\AuditRecorder;
use Illuminate\Support\Facades\Notification;

final class NotifyOverdueCheckouts
{
    public function __invoke(): void
    {
        Visit::query()
            ->with([
                'host',
                'department.receptionist',
                'visitors' => fn ($query) => $query
                    ->wherePivotNotNull('checked_in_at')
                    ->wherePivotNull('checked_out_at'),
            ])
            ->where('scheduled_until', '<', now())
            ->whereHas('visitors', fn ($query) => $query
                ->whereNotNull('visit_visitor.checked_in_at')
                ->whereNull('visit_visitor.checked_out_at'))
            ->chunkById(100, function ($visits): void {
                foreach ($visits as $visit) {
                    foreach ($visit->visitors as $visitor) {
                        $this->notifyOnce($visit, $visitor);
                    }
                }
            });
    }

    private function notifyOnce(Visit $visit, Visitor $visitor): void
    {
        $alreadyAlerted = AuditEvent::query()
            ->where('event', 'visit.participant.overdue_alerted')
            ->whereMorphedTo('auditable', $visit)
            ->whereMorphedTo('subject', $visitor)
            ->where('occurred_at', '>=', $visitor->pivot->checked_in_at)
            ->exists();

        if ($alreadyAlerted) {
            return;
        }

        $recipients = collect([$visit->host, $visit->department?->receptionist])
            ->filter(fn ($recipient) => $recipient instanceof User && $recipient->is_active)
            ->unique('id');

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new OverdueCheckoutNotification($visit, $visitor));
        }

        app(AuditRecorder::class)->record(
            'visit.participant.overdue_alerted',
            $visit,
            subject: $visitor,
            siteId: $visit->site_id,
            metadata: ['recipient_count' => $recipients->count()],
        );
    }
}