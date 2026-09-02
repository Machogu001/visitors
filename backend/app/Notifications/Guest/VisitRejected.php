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

class VisitRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Visit $visit,
        public ?string $reason = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $timezone = $this->visit->site->timezone ?: config('app.timezone', 'Africa/Nairobi');
        $scheduledFromLocal = $this->visit->scheduled_from->setTimezone($timezone);

        $message = (new MailMessage)
            ->subject(__('Your appointment request was not approved'))
            ->line(__('Unfortunately, your appointment request could not be approved.'))
            ->line('')
            ->line(__('Requested appointment details:'))
            ->line('')
            ->line(__('Department: :department', ['department' => $this->visit->department?->name ?: __('Reception')]))
            ->line(__('Host: :host', ['host' => $this->visit->host->name]))
            ->line(__('Date & Time: :datetime', [
                'datetime' => $scheduledFromLocal->format(__('l, F j, Y \a\t g:i A')),
            ]))
            ->line(__('Booking Code: :code', ['code' => $this->visit->booking_reference]));

        if (filled($this->reason)) {
            $message->line('')
                ->line(__('Reason: :reason', ['reason' => $this->reason]));
        }

        return $message
            ->line('')
            ->line(__('If you have any questions, please contact the department directly.'));
    }
}
