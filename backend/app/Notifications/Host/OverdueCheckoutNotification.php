<?php

namespace App\Notifications\Host;

use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Support\PortalNotificationData;
use Illuminate\Notifications\Notification;

final class OverdueCheckoutNotification extends Notification
{
    public function __construct(private Visit $visit, private Visitor $visitor) {}

    public function via(User $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $name = trim($this->visitor->first_name.' '.$this->visitor->name) ?: __('Guest');

        return PortalNotificationData::make(
            type: 'overdue_checkout',
            titleKey: 'Overdue check-out',
            messageKey: ':name is still checked in after the scheduled visit end.',
            messageReplacements: ['name' => $name],
            actionUrl: route('portal.visits.show', $this->visit, absolute: false),
            actionLabelKey: 'Review visit',
            context: ['visit_id' => $this->visit->id, 'visitor_id' => $this->visitor->id],
        );
    }
}