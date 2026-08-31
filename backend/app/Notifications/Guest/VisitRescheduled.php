<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Notifications\Guest;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitRescheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Visit $visit,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $timezone = $this->visit->site->timezone ?: config('app.timezone', 'Africa/Nairobi');
        $scheduledFromLocal = $this->visit->scheduled_from->setTimezone($timezone);
        $scheduledUntilLocal = $this->visit->scheduled_until->setTimezone($timezone);

        return (new MailMessage)
            ->subject(__('Your appointment has been rescheduled'))
            ->line(__('Your visit appointment has been rescheduled by the department.'))
            ->line('')
            ->line(__('New appointment details:'))
            ->line('')
            ->line(__('Department: :department', ['department' => $this->visit->department?->name ?: __('Reception')]))
            ->line(__('Host: :host', ['host' => $this->visit->host->name]))
            ->line(__('New Date & Time: :datetime', [
                'datetime' => $scheduledFromLocal->format(__('l, F j, Y \a\t g:i A')),
            ]))
            ->line(__('Duration: :minutes minutes', ['minutes' => $this->visit->scheduled_until->diffInMinutes($this->visit->scheduled_from)]))
            ->line('')
            ->line(__('Booking Code: :code', ['code' => $this->visit->booking_reference]))
            ->line('')
            ->action(__('View Booking Details'), url('/'))
            ->line(__('If you have any questions or need to reschedule again, please contact the department directly.'));
    }
}
