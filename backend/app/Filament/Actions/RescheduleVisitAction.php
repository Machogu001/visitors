<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Filament\Actions;

use App\Models\Visit;
use App\Services\BookingAvailabilityService;
use App\Services\VisitActionService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class RescheduleVisitAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reschedule';
    }

    public static function make(?string $name = null): static
    {
        $name ??= static::getDefaultName();
        $action = parent::make($name);

        $action
            ->label(__('Reschedule'))
            ->icon('heroicon-m-calendar')
            ->color('info')
            ->form([
                DatePicker::make('newDate')
                    ->label(__('New Date'))
                    ->required()
                    ->minDate(now()->addDay()),

                TextInput::make('newTime')
                    ->label(__('New Time'))
                    ->type('time')
                    ->required(),
            ])
            ->action(function (Visit $record, array $data, VisitActionService $actionService, BookingAvailabilityService $availabilityService) {
                try {
                    // Check availability
                    $newDate = $data['newDate'];
                    $newTime = $data['newTime'];

                    $timezone = $record->site->timezone ?: config('app.timezone', 'Africa/Nairobi');
                    $newScheduledFrom = \Illuminate\Support\Carbon::parse($newDate.' '.$newTime, $timezone);

                    // Check if slot is available
                    $availableSlots = $availabilityService->getAvailableSlots(
                        $record->department_id,
                        $newDate,
                        $record->site_id
                    );

                    $isAvailable = $availableSlots->some(function ($slot) use ($newScheduledFrom) {
                        $slotStart = \Illuminate\Support\Carbon::parse($slot['start']);
                        $slotEnd = \Illuminate\Support\Carbon::parse($slot['end']);

                        return $newScheduledFrom->isBetween($slotStart, $slotEnd);
                    });

                    if (! $isAvailable) {
                        Notification::make()
                            ->danger()
                            ->title(__('Time slot not available'))
                            ->body(__('The selected time slot is not available. Please choose another time.'))
                            ->send();

                        return;
                    }

                    // Reschedule the visit
                    $actionService->rescheduleVisit(
                        $record,
                        auth()->user(),
                        $newDate,
                        $newTime
                    );

                    Notification::make()
                        ->success()
                        ->title(__('Visit rescheduled'))
                        ->body(__('The visit has been rescheduled and the visitor has been notified.'))
                        ->send();
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Reschedule visit failed: '.$e->getMessage());
                    Notification::make()
                        ->danger()
                        ->title(__('Error'))
                        ->body(__('Failed to reschedule visit: '.$e->getMessage()))
                        ->send();
                }
            });

        return $action;
    }
}
