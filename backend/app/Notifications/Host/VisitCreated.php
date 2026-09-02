<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Notifications\Host;

use App\Mail\Host\VisitCreatedMail;
use App\Models\User;
use App\Models\Visit;
use App\Support\PortalNotificationData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class VisitCreated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Visit $visit,
        protected Collection $visitors,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): Mailable
    {
        Log::channel('mail')->info('Notifying Host(User) per E-Mail, that a visit was created', [
            'user_id' => $notifiable->id,
            'visit_id' => $this->visit->id,
            'visitor_count' => $this->visitors->count(),
            'class' => get_class(),
        ]);

        return (new VisitCreatedMail($this->visit, $this->visitors, $notifiable))->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $visitorName = trim(implode(', ', $this->visitors->map(
            fn ($visitor) => trim(($visitor->first_name ?? '').' '.($visitor->name ?? ''))
        )->filter()->all()));

        return PortalNotificationData::make(
            type: 'visit_created',
            titleKey: 'New appointment booked',
            messageKey: 'A new appointment with :name has been booked.',
            messageReplacements: [
                'name' => $visitorName !== '' ? $visitorName : __('a visitor'),
            ],
            actionUrl: route('portal.visits.show', $this->visit->getKey(), absolute: false),
            actionLabelKey: 'View appointment',
            context: [
                'visit_id' => $this->visit->id,
            ],
        );
    }
}
