<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Notifications\Host;

use App\Models\Visit;
use App\Support\PortalNotificationData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class VisitRescheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Visit $visit,
        public Collection $visitors,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $timezone = $this->visit->site->timezone ?: config('app.timezone', 'Africa/Nairobi');
        $scheduledFromLocal = $this->visit->scheduled_from->setTimezone($timezone);
        $visitorNames = $this->visitors->map(fn ($visitor) => trim($visitor->first_name.' '.$visitor->name))->implode(', ');

        return (new MailMessage)
            ->subject(__('A visit has been rescheduled'))
            ->line(__('A visit you are hosting has been rescheduled.'))
            ->line('')
            ->line(__('Visitor(s): :names', ['names' => $visitorNames ?: __('Unknown')]))
            ->line(__('Department: :department', ['department' => $this->visit->department?->name ?: __('Reception')]))
            ->line(__('New Date & Time: :datetime', [
                'datetime' => $scheduledFromLocal->format(__('l, F j, Y \a\t g:i A')),
            ]))
            ->line(__('Duration: :minutes minutes', ['minutes' => $this->visit->scheduled_until->diffInMinutes($this->visit->scheduled_from)]))
            ->line('')
            ->line(__('Booking Code: :code', ['code' => $this->visit->booking_reference]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $timezone = $this->visit->site->timezone ?: config('app.timezone', 'Africa/Nairobi');
        $scheduledFromLocal = $this->visit->scheduled_from->setTimezone($timezone);
        $visitorNames = $this->visitors->map(fn ($visitor) => trim($visitor->first_name.' '.$visitor->name))->implode(', ');

        return PortalNotificationData::make(
            type: 'visit_rescheduled',
            titleKey: 'Appointment rescheduled',
            messageKey: 'The appointment with :name has been moved to :datetime.',
            messageReplacements: [
                'name' => $visitorNames !== '' ? $visitorNames : __('a visitor'),
                'datetime' => $scheduledFromLocal->format(__('l, F j, Y \a\t g:i A')),
            ],
            actionUrl: route('portal.visits.show', $this->visit->getKey(), absolute: false),
            actionLabelKey: 'View appointment',
            context: [
                'visit_id' => $this->visit->id,
            ],
        );
    }
}
