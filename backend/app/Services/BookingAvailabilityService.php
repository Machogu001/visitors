<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Services;

use App\Enums\VisitStatusEnum;
use App\Models\Department;
use App\Models\Site;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class BookingAvailabilityService
{
    /**
     * Working hours start (08:30).
     */
    public const WORK_DAY_START_HOUR = 8;
    public const WORK_DAY_START_MINUTE = 30;

    /**
     * Working hours end (17:00).
     */
    public const WORK_DAY_END_HOUR = 17;
    public const WORK_DAY_END_MINUTE = 0;

    /**
     * Slot interval in minutes.
     */
    public const DEFAULT_SLOT_INTERVAL_MINUTES = 30;

    /**
     * Get active bookable sites.
     *
     * @return Collection<int, Site>
     */
    public function getBookableSites(): Collection
    {
        $hasBookingCols = Schema::hasColumn('sites', 'allow_general_booking');

        return Site::query()
            ->where('is_active', true)
            ->when($hasBookingCols, function ($query): void {
                $query->where(function ($q): void {
                    $q->where('allow_general_booking', true)
                        ->orWhere('allow_department_booking', true);
                });
            })
            ->with([
                'generalBookingHost:id,first_name,name,title,email',
                'departments' => function ($query): void {
                    $hasDeptBookingCol = Schema::hasColumn('departments', 'allow_public_booking');
                    $query->where('is_active', true)
                        ->when($hasDeptBookingCol, fn ($q) => $q->where('allow_public_booking', true))
                        ->with('headUser:id,first_name,name,title,email');
                },
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get bookable departments for a given site.
     *
     * @return Collection<int, Department>
     */
    public function getBookableDepartmentsForSite(int $siteId): Collection
    {
        $hasDeptBookingCol = Schema::hasColumn('departments', 'allow_public_booking');

        return Department::query()
            ->where('site_id', $siteId)
            ->where('is_active', true)
            ->when($hasDeptBookingCol, fn ($q) => $q->where('allow_public_booking', true))
            ->with(['headUser:id,first_name,name,title,email'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Resolve effective host for a general booking at a site.
     */
    public function resolveGeneralBookingHost(Site $site): ?User
    {
        if ($site->general_booking_host_id && $site->generalBookingHost?->is_active) {
            return $site->generalBookingHost;
        }

        return User::query()
            ->where('is_active', true)
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['welcome monitor', 'welcome_monitor']))
            ->where(function ($query) use ($site): void {
                $query->where('site_id', $site->id)
                    ->orWhereHas('sites', fn ($q) => $q->whereKey($site->id));
            })
            ->orderBy('id')
            ->first();
    }

    /**
     * Resolve host for department booking.
     */
    public function resolveDepartmentHost(Department $department): ?User
    {
        if ($department->head_user_id && $department->headUser?->is_active) {
            return $department->headUser;
        }

        return User::query()
            ->where('department_id', $department->id)
            ->where('is_active', true)
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['welcome monitor', 'welcome_monitor']))
            ->orderBy('id')
            ->first();
    }

    /**
     * Generate available time slots for a host on a given date.
     *
     * @return array<int, array{time: string, label: string, available: bool}>
     */
    public function getAvailableSlotsForHost(
        User $host,
        Site $site,
        string|Carbon $date,
        int $durationMinutes = 30
    ): array {
        $timezone = $site->timezone ?: config('app.timezone', 'Africa/Nairobi');
        $dateObj = is_string($date) ? Carbon::parse($date, $timezone) : $date->copy()->setTimezone($timezone);

        if ($dateObj->isWeekend()) {
            return [];
        }

        $now = Carbon::now($timezone);
        $dayStart = $dateObj->copy()->setTime(self::WORK_DAY_START_HOUR, self::WORK_DAY_START_MINUTE, 0);
        $dayEnd = $dateObj->copy()->setTime(self::WORK_DAY_END_HOUR, self::WORK_DAY_END_MINUTE, 0);

        $conflictingVisits = Visit::query()
            ->where(function ($query) use ($host): void {
                $query->where('host_user_id', $host->id)
                    ->orWhere('substitute_user_id', $host->id);
            })
            ->where('status', '!=', VisitStatusEnum::Canceled->value)
            ->where(function ($query) use ($dayStart, $dayEnd): void {
                $query->where('scheduled_from', '<', $dayEnd)
                    ->where('scheduled_until', '>', $dayStart);
            })
            ->get(['scheduled_from', 'scheduled_until']);

        $slots = [];
        $current = $dayStart->copy();

        while ($current->copy()->addMinutes($durationMinutes)->lte($dayEnd)) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addMinutes($durationMinutes);

            $isPast = $slotStart->lte($now->copy()->addMinutes(15));

            $hasConflict = false;
            foreach ($conflictingVisits as $visit) {
                $visitStart = $visit->scheduled_from->copy()->setTimezone($timezone);
                $visitEnd = $visit->scheduled_until->copy()->setTimezone($timezone);

                if ($slotStart->lt($visitEnd) && $slotEnd->gt($visitStart)) {
                    $hasConflict = true;
                    break;
                }
            }

            $slots[] = [
                'time' => $slotStart->format('H:i'),
                'label' => sprintf('%s - %s', $slotStart->format('H:i'), $slotEnd->format('H:i')),
                'available' => ! $isPast && ! $hasConflict,
            ];

            $current->addMinutes(self::DEFAULT_SLOT_INTERVAL_MINUTES);
        }

        return $slots;
    }

    /**
     * Get selectable upcoming business days.
     *
     * @return array<int, array{date: string, label: string, day_name: string}>
     */
    public function getSelectableDates(string $timezone = 'Africa/Nairobi', int $daysAhead = 21): array
    {
        $dates = [];
        $current = Carbon::now($timezone)->addDay();
        $count = 0;

        while ($count < $daysAhead && count($dates) < 14) {
            if (! $current->isWeekend()) {
                $dates[] = [
                    'date' => $current->format('Y-m-d'),
                    'label' => $current->isoFormat('DD.MM.YYYY'),
                    'day_name' => $current->translatedFormat('l'),
                ];
            }
            $current->addDay();
            $count++;
        }

        return $dates;
    }
}
