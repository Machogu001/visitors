<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Services;

use App\Enums\VisitStatusEnum;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Notifications\Host\GuestCheckedInDatabaseNotification;
use App\Notifications\Host\GuestCheckedInMailNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class VisitActionService
{
    public function __construct(private readonly AuditRecorder $auditRecorder) {}

    public function checkInParticipant(Visit $visit, Visitor $visitor, ?User $actionBy): Visitor
    {
        [$participant, $didCheckIn] = DB::transaction(function () use ($visit, $visitor, $actionBy): array {
            $lockedVisit = Visit::query()->lockForUpdate()->findOrFail($visit->getKey());
            $this->ensureVisitCanBeOperated($lockedVisit);
            $pivot = $this->lockedParticipantPivot($lockedVisit, $visitor);
            $wasReentry = filled($pivot->checked_out_at);
            $didCheckIn = blank($pivot->checked_in_at) || $wasReentry;

            if ($didCheckIn) {
                $payload = [
                    'checked_in_at' => now(),
                    'checked_in_by_user_id' => $actionBy?->id,
                    'updated_at' => now(),
                ];

                if ($wasReentry) {
                    $payload['checked_out_at'] = null;
                    $payload['checked_out_by_user_id'] = null;
                }

                DB::table('visit_visitor')
                    ->where('visit_id', $lockedVisit->id)
                    ->where('visitor_id', $visitor->id)
                    ->update($payload);

                $this->auditRecorder->record(
                    'visit.participant.checked_in',
                    $lockedVisit,
                    $actionBy,
                    $visitor,
                    $lockedVisit->site_id,
                    ['was_reentry' => $wasReentry],
                );
            }

            return [$this->resolveParticipant($lockedVisit, $visitor), $didCheckIn];
        });

        if ($didCheckIn) {
            $this->notifyHostAboutCheckIn($visit, $participant);
        }

        return $participant;
    }

    public function checkOutParticipant(Visit $visit, Visitor $visitor, User $actionBy): Visitor
    {
        return DB::transaction(function () use ($visit, $visitor, $actionBy): Visitor {
            $lockedVisit = Visit::query()->lockForUpdate()->findOrFail($visit->getKey());
            $this->ensureVisitCanBeOperated($lockedVisit);
            $this->ensureChequeCollectionIsSigned($lockedVisit);
            $pivot = $this->lockedParticipantPivot($lockedVisit, $visitor);

            if (filled($pivot->checked_in_at) && blank($pivot->checked_out_at)) {
                DB::table('visit_visitor')
                    ->where('visit_id', $lockedVisit->id)
                    ->where('visitor_id', $visitor->id)
                    ->update([
                        'checked_out_at' => now(),
                        'checked_out_by_user_id' => $actionBy->id,
                        'updated_at' => now(),
                    ]);

                $this->auditRecorder->record(
                    'visit.participant.checked_out',
                    $lockedVisit,
                    $actionBy,
                    $visitor,
                    $lockedVisit->site_id,
                );
            }

            return $this->resolveParticipant($lockedVisit, $visitor);
        });
    }

    public function printBadge(Visit $visit, Visitor $visitor, ?User $actionBy = null): Visitor
    {
        return DB::transaction(function () use ($visit, $visitor, $actionBy): Visitor {
            $lockedVisit = Visit::query()->lockForUpdate()->findOrFail($visit->getKey());
            $this->ensureVisitCanBeOperated($lockedVisit);
            $pivot = $this->lockedParticipantPivot($lockedVisit, $visitor);

            if (blank($pivot->badge_printed_at)) {
                DB::table('visit_visitor')
                    ->where('visit_id', $lockedVisit->id)
                    ->where('visitor_id', $visitor->id)
                    ->update([
                        'badge_printed_at' => now(),
                        'updated_at' => now(),
                    ]);

                $this->auditRecorder->record(
                    'visit.participant.badge_printed',
                    $lockedVisit,
                    $actionBy ?? (auth()->user() instanceof User ? auth()->user() : null),
                    $visitor,
                    $lockedVisit->site_id,
                );
            }

            return $this->resolveParticipant($lockedVisit, $visitor);
        });
    }

    public function cancelCheckIn(Visit $visit, Visitor $visitor, ?User $actionBy = null): Visitor
    {
        return DB::transaction(function () use ($visit, $visitor, $actionBy): Visitor {
            $lockedVisit = Visit::query()->lockForUpdate()->findOrFail($visit->getKey());
            $pivot = $this->lockedParticipantPivot($lockedVisit, $visitor);

            if (filled($pivot->checked_in_at) || filled($pivot->checked_out_at)) {
                DB::table('visit_visitor')
                    ->where('visit_id', $lockedVisit->id)
                    ->where('visitor_id', $visitor->id)
                    ->update([
                        'checked_in_at' => null,
                        'checked_in_by_user_id' => null,
                        'checked_out_at' => null,
                        'checked_out_by_user_id' => null,
                        'updated_at' => now(),
                    ]);

                $this->auditRecorder->record(
                    'visit.participant.check_in_canceled',
                    $lockedVisit,
                    $actionBy ?? (auth()->user() instanceof User ? auth()->user() : null),
                    $visitor,
                    $lockedVisit->site_id,
                );
            }

            return $this->resolveParticipant($lockedVisit, $visitor);
        });
    }

    private function resolveParticipant(Visit $visit, Visitor $visitor): Visitor
    {
        return $visit->visitors()
            ->where('visitors.id', $visitor->id)
            ->firstOrFail();
    }

    private function lockedParticipantPivot(Visit $visit, Visitor $visitor): object
    {
        return DB::table('visit_visitor')
            ->where('visit_id', $visit->id)
            ->where('visitor_id', $visitor->id)
            ->lockForUpdate()
            ->first() ?? throw (new ModelNotFoundException)->setModel(Visitor::class, [$visitor->id]);
    }

    public function canOperate(Visit $visit): bool
    {
        return $visit->status === VisitStatusEnum::Planned->value;
    }

    private function ensureVisitCanBeOperated(Visit $visit): void
    {
        if ($this->canOperate($visit)) {
            return;
        }

        throw ValidationException::withMessages([
            'visit' => __('Dieser Besuch kann operativ nicht mehr bearbeitet werden.'),
        ]);
    }

    private function ensureChequeCollectionIsSigned(Visit $visit): void
    {
        if ($visit->cheque_action !== 'pick_up') {
            return;
        }

        $missingDetails = blank($visit->cheque_number)
            || blank($visit->cheque_amount)
            || blank($visit->cheque_bank)
            || blank($visit->cheque_payee_or_drawer)
            || blank($visit->signature_data);

        if ($missingDetails) {
            throw ValidationException::withMessages([
                'checkout' => __('Cheque collection details and visitor signature are required before check-out.'),
            ]);
        }
    }

    public function approveVisit(Visit $visit, User $actionBy): Visit
    {
        $visit->update([
            'status' => VisitStatusEnum::Planned->value,
            'approved_at' => now(),
            'approved_by_user_id' => $actionBy->id,
            'rejected_at' => null,
            'rejected_by_user_id' => null,
            'rejection_reason' => null,
        ]);

        $this->auditRecorder->record('visit.approved', $visit, $actionBy, siteId: $visit->site_id);

        // Send confirmation email to guest
        $visit->loadMissing(['visitors', 'host', 'department', 'site']);
        foreach ($visit->visitors as $guest) {
            try {
                if ($guest->email) {
                    $guest->notify(new \App\Notifications\Guest\VisitCreated($visit));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::channel('mail')->warning('Failed sending guest approval notification: '.$e->getMessage());
            }
        }

        return $visit->refresh();
    }

    public function rejectVisit(Visit $visit, User $actionBy, ?string $reason = null): Visit
    {
        $visit->update([
            'status' => VisitStatusEnum::Rejected->value,
            'rejected_at' => now(),
            'rejected_by_user_id' => $actionBy->id,
            'rejection_reason' => $reason,
        ]);

        $this->auditRecorder->record(
            'visit.rejected',
            $visit,
            $actionBy,
            siteId: $visit->site_id,
            metadata: ['reason_provided' => filled($reason)],
        );

        $visit->loadMissing(['visitors', 'host', 'department', 'site']);
        foreach ($visit->visitors as $guest) {
            try {
                if ($guest->email) {
                    $guest->notify(new \App\Notifications\Guest\VisitRejected($visit, $reason));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::channel('mail')->warning('Failed sending guest rejection notification: '.$e->getMessage());
            }
        }

        return $visit->refresh();
    }

    public function usherVisit(Visit $visit, User $actionBy): Visit
    {
        $visit->update([
            'ushered_at' => now(),
            'ushered_by_user_id' => $actionBy->id,
        ]);

        $this->auditRecorder->record('visit.ushered', $visit, $actionBy, siteId: $visit->site_id);

        return $visit->refresh();
    }

    public function recordChequeDetails(Visit $visit, array $data): Visit
    {
        $visit->update([
            'cheque_action' => $data['cheque_action'] ?? $visit->cheque_action,
            'cheque_number' => $data['cheque_number'] ?? $visit->cheque_number,
            'cheque_amount' => ! empty($data['cheque_amount']) ? (float) $data['cheque_amount'] : $visit->cheque_amount,
            'cheque_bank' => $data['cheque_bank'] ?? $visit->cheque_bank,
            'cheque_payee_or_drawer' => $data['cheque_payee_or_drawer'] ?? $visit->cheque_payee_or_drawer,
            'signature_data' => $data['signature_data'] ?? $visit->signature_data,
            'signed_by_name' => $data['signed_by_name'] ?? $visit->signed_by_name,
            'signed_at' => ! empty($data['signature_data']) ? now() : $visit->signed_at,
        ]);

        $this->auditRecorder->record(
            'visit.cheque_details_recorded',
            $visit,
            auth()->user() instanceof User ? auth()->user() : null,
            siteId: $visit->site_id,
            metadata: ['cheque_action' => (string) $visit->cheque_action],
        );

        return $visit->refresh();
    }

    private function notifyHostAboutCheckIn(Visit $visit, Visitor $visitor): void
    {
        $visit->loadMissing(['host', 'department.receptionist']);

        $recipients = collect();

        if ($visit->host instanceof User && $visit->host->is_active) {
            $recipients->push($visit->host);
        }

        // Tier 2: If department has a dedicated receptionist / assistant (e.g. Director's Receptionist), notify them too!
        if ($visit->department?->receptionist instanceof User && $visit->department->receptionist->is_active) {
            $recipients->push($visit->department->receptionist);
        }

        foreach ($recipients->unique('id') as $recipient) {
            Notification::send($recipient, new GuestCheckedInDatabaseNotification($visit, $visitor));
            Notification::send($recipient, new GuestCheckedInMailNotification($visit, $visitor));
        }
    }

    /**
     * Reschedule a visit to a new date and time.
     *
     * @param  Visit  $visit  The visit to reschedule
     * @param  User  $actionBy  The user performing the reschedule (typically host)
     * @param  string  $newDate  New date in YYYY-MM-DD format
     * @param  string  $newTime  New time in HH:MM format
     * @param  int  $durationMinutes  Duration in minutes (default: keep original)
     * @return Visit  Updated visit with new schedule
     */
    public function rescheduleVisit(Visit $visit, User $actionBy, string $newDate, string $newTime, int $durationMinutes = 0): Visit
    {
        $timezone = $visit->site->timezone ?: config('app.timezone', 'Africa/Nairobi');
        $previousScheduledFrom = $visit->scheduled_from->toIso8601String();
        $previousScheduledUntil = $visit->scheduled_until->toIso8601String();

        // Parse new schedule in the site's timezone, convert to UTC
        $newScheduledFrom = \Illuminate\Support\Carbon::parse($newDate.' '.$newTime, $timezone)->setTimezone('UTC');
        $duration = $durationMinutes ?: ($visit->scheduled_until->diffInMinutes($visit->scheduled_from));
        $newScheduledUntil = $newScheduledFrom->copy()->addMinutes($duration);

        // Update the visit with new schedule
        $visit->update([
            'scheduled_from' => $newScheduledFrom,
            'scheduled_until' => $newScheduledUntil,
            'rescheduled_at' => now(),
            'rescheduled_by_user_id' => $actionBy->id,
        ]);

        $this->auditRecorder->record(
            'visit.rescheduled',
            $visit,
            $actionBy,
            siteId: $visit->site_id,
            metadata: [
                'scheduled_from_before' => $previousScheduledFrom,
                'scheduled_until_before' => $previousScheduledUntil,
                'scheduled_from_after' => $newScheduledFrom->toIso8601String(),
                'scheduled_until_after' => $newScheduledUntil->toIso8601String(),
            ],
        );

        // Notify all visitors about the reschedule
        $visit->loadMissing(['visitors', 'host', 'department.receptionist', 'site']);
        foreach ($visit->visitors as $visitor) {
            try {
                if ($visitor->email) {
                    $visitor->notify(new \App\Notifications\Guest\VisitRescheduled($visit));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::channel('mail')->warning('Failed sending visit reschedule notification: '.$e->getMessage());
            }
        }

        // Notify the host (and department receptionist, if any) about the reschedule
        $recipients = collect();

        if ($visit->host instanceof User && $visit->host->is_active && $visit->host->id !== $actionBy->id) {
            $recipients->push($visit->host);
        }

        if ($visit->department?->receptionist instanceof User && $visit->department->receptionist->is_active && $visit->department->receptionist->id !== $actionBy->id) {
            $recipients->push($visit->department->receptionist);
        }

        foreach ($recipients->unique('id') as $recipient) {
            try {
                if ($recipient->email) {
                    $recipient->notify(new \App\Notifications\Host\VisitRescheduled($visit, $visit->visitors));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::channel('mail')->warning('Failed sending host reschedule notification: '.$e->getMessage());
            }
        }

        return $visit->refresh();
    }
}
