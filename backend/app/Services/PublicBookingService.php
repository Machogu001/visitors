<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Services;

use App\Enums\SalutationEnum;
use App\Enums\VisitStatusEnum;
use App\Models\Department;
use App\Models\Site;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Notifications\Guest\VisitCreated as GuestVisitCreatedNotification;
use App\Notifications\Host\VisitCreated as HostVisitCreatedNotification;
use App\Rules\ValidPhoneNumber;
use App\Support\PhoneNumber;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PublicBookingService
{
    /**
     * Create a public appointment booking.
     *
     * @param array{
     *     site_id: int,
     *     booking_type: string,
     *     department_id?: ?int,
     *     purpose?: ?string,
     *     date: string,
     *     time: string,
     *     duration_minutes: int,
     *     first_name: string,
     *     name: string,
     *     email: string,
      *     phone: string,
      *     company: string,
     *     salutation?: ?string,
    *     notes: string,
    *     cheque_action?: ?string,
    *     cheque_number?: ?string,
    *     cheque_amount?: mixed,
    *     cheque_bank?: ?string,
    *     cheque_payee_or_drawer?: ?string,
    *     signature_data?: ?string,
    *     signed_by_name?: ?string
     * } $data
     */
    public function createBooking(array $data): Visit
    {
          $data = $this->validateBookingData($data);

        return DB::transaction(function () use ($data): Visit {
            $site = Site::query()->findOrFail($data['site_id']);
            $timezone = $site->timezone ?: config('app.timezone', 'Africa/Nairobi');

            $availabilityService = app(BookingAvailabilityService::class);
            $department = null;
            $host = null;

            if ($data['booking_type'] === 'department_head' && ! empty($data['department_id'])) {
                $department = Department::query()->where('site_id', $site->id)->findOrFail($data['department_id']);
                $host = $availabilityService->resolveDepartmentHost($department);
            } else {
                $host = $availabilityService->resolveGeneralBookingHost($site);
            }

            if (! $host) {
                $host = User::query()
                    ->where('is_active', true)
                    ->where(function ($query) use ($site): void {
                        $query->where('site_id', $site->id)
                            ->orWhereHas('sites', fn ($q) => $q->whereKey($site->id));
                    })
                    ->firstOrFail();
            }

            $scheduledFrom = Carbon::parse($data['date'].' '.$data['time'], $timezone)->setTimezone('UTC');
            $duration = (int) ($data['duration_minutes'] ?? 30);
            $scheduledUntil = $scheduledFrom->copy()->addMinutes($duration);

            $bookingReference = $this->generateUniqueBookingReference();

            $salutation = match ($data['salutation'] ?? null) {
                'mr', 'Herr' => SalutationEnum::Mr,
                'ms', 'Frau' => SalutationEnum::Ms,
                default => SalutationEnum::NotSpecified,
            };

            $phone = ! empty($data['phone']) ? PhoneNumber::normalize($data['phone']) : null;

            $visitor = Visitor::query()->updateOrCreate(
                ['email' => strtolower(trim($data['email']))],
                [
                    'first_name' => trim($data['first_name']),
                    'name' => trim($data['name']),
                    'salutation' => $salutation,
                    'phone' => $phone,
                    'id_number' => ! empty($data['id_number']) ? trim($data['id_number']) : null,
                    'company' => ! empty($data['company']) ? trim($data['company']) : null,
                    'created_by_user_id' => $host->id,
                ]
            );

            $purpose = $data['purpose'] ?? ($department ? $department->name : __('General Appointment'));
            $isChequeDropOff = ($data['cheque_action'] ?? null) === 'drop_off';
            $isChequeBooking = in_array($data['cheque_action'] ?? null, ['drop_off', 'pick_up'], true);
            $title = sprintf(
                '%s: %s %s (%s)',
                __('Appointment'),
                $visitor->first_name,
                $visitor->name,
                $department ? $department->name : ($data['purpose'] ?? __('General'))
            );

            $requiresApproval = $department ? ($department->requires_approval ?? true) : false;
            $initialStatus = $requiresApproval ? VisitStatusEnum::PendingApproval->value : VisitStatusEnum::Planned->value;

            $visit = Visit::create([
                'booking_reference' => $bookingReference,
                'site_id' => $site->id,
                'department_id' => $department?->id,
                'host_user_id' => $host->id,
                'created_by_user_id' => $host->id,
                'booking_type' => $data['booking_type'],
                'purpose' => $purpose,
                'title' => $title,
                'scheduled_from' => $scheduledFrom,
                'scheduled_until' => $scheduledUntil,
                'status' => $initialStatus,
                'is_confidential' => false,
                'is_walk_in' => false,
                'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                'visitor_id_number' => ! empty($data['id_number']) ? trim($data['id_number']) : null,
                'cheque_action' => $data['cheque_action'] ?? null,
                'cheque_number' => $isChequeDropOff ? trim($data['cheque_number']) : null,
                'cheque_amount' => $isChequeDropOff ? (float) $data['cheque_amount'] : null,
                'cheque_bank' => $isChequeDropOff ? trim($data['cheque_bank']) : null,
                'cheque_payee_or_drawer' => $isChequeBooking ? trim($data['cheque_payee_or_drawer']) : null,
                'signature_data' => $isChequeDropOff ? $data['signature_data'] : null,
                'signed_at' => $isChequeDropOff ? now() : null,
                'signed_by_name' => $isChequeDropOff ? trim($data['signed_by_name'] ?? '') : null,
            ]);

            $visit->visitors()->attach($visitor->id);

            $this->sendNotificationsSafely($visit, $visitor, $host);

            Log::channel('web')->info('Public appointment booked', [
                'visit_id' => $visit->id,
                'booking_reference' => $bookingReference,
                'site_id' => $site->id,
                'department_id' => $department?->id,
                'host_user_id' => $host->id,
                'visitor_id' => $visitor->id,
            ]);

            return $visit;
        });
    }

    public function generateUniqueBookingReference(): string
    {
        do {
            $reference = 'BK-' . strtoupper(Str::random(6));
        } while (Visit::query()->where('booking_reference', $reference)->exists());

        return $reference;
    }

    private function validateBookingData(array $data): array
    {
        foreach (['phone', 'company', 'notes', 'cheque_action', 'cheque_number', 'cheque_amount', 'cheque_bank', 'cheque_payee_or_drawer', 'signature_data', 'signed_by_name'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        $rules = [
            'phone' => ['required', 'string', 'max:50', new ValidPhoneNumber],
            'company' => ['required', 'string', 'max:255'],
            'notes' => ['required', 'string', 'max:1000'],
            'cheque_action' => ['nullable', 'in:drop_off,pick_up'],
        ];

        if (($data['cheque_action'] ?? null) === 'drop_off') {
            $rules['cheque_number'] = ['required', 'string', 'max:100'];
            $rules['cheque_amount'] = ['required', 'numeric', 'min:0.01'];
            $rules['cheque_bank'] = ['required', 'string', 'max:150'];
            $rules['cheque_payee_or_drawer'] = ['required', 'string', 'max:200'];
            $rules['signature_data'] = ['required', 'string'];
        } elseif (($data['cheque_action'] ?? null) === 'pick_up') {
            $rules['cheque_payee_or_drawer'] = ['required', 'string', 'max:200'];
        }

        Validator::make($data, $rules, [
            'phone.required' => __('Phone number is required.'),
            'company.required' => __('Please enter your company or organization name.'),
            'notes.required' => __('Please enter the topic or message for the host.'),
            'cheque_number.required' => __('Please enter the cheque number.'),
            'cheque_amount.required' => __('Please enter the cheque amount.'),
            'cheque_bank.required' => __('Please enter the bank name.'),
            'cheque_payee_or_drawer.required' => __('Please enter the drawer or account name.'),
            'signature_data.required' => __('Please sign to acknowledge the cheque details.'),
        ])->validate();

        return $data;
    }

    private function sendNotificationsSafely(Visit $visit, Visitor $visitor, User $host): void
    {
        try {
            if ($visitor->email) {
                $visitor->notify(new GuestVisitCreatedNotification($visit));
            }
        } catch (Exception $e) {
            Log::channel('mail')->warning('Failed sending guest booking confirmation email: ' . $e->getMessage());
        }

        try {
            if ($host->email) {
                $host->notify(new HostVisitCreatedNotification($visit, collect([$visitor])));
            }
        } catch (Exception $e) {
            Log::channel('mail')->warning('Failed sending host booking notification email: ' . $e->getMessage());
        }
    }

    public function generateIcs(Visit $visit): string
    {
        $visit->loadMissing(['host', 'department', 'site', 'visitors']);

        $site = $visit->site;
        $host = $visit->host;
        $location = trim(($site?->name ?? '') . ($site?->address ? ', ' . $site->address : ''));
        $title = $visit->title ?? __('Appointment');
        $description = sprintf(
            "%s\\n%s: %s\\n%s: %s\\n%s: %s",
            __('Appointment booked via VisitorPortal'),
            __('Host'),
            $host?->fullName ?? '-',
            __('Department'),
            $visit->department?->name ?? __('General'),
            __('Booking code'),
            $visit->booking_reference ?? '-'
        );

        $dtStart = $visit->scheduled_from->format('Ymd\THis\Z');
        $dtEnd = $visit->scheduled_until->format('Ymd\THis\Z');
        $dtStamp = now()->format('Ymd\THis\Z');
        $uid = ($visit->booking_reference ?: 'VISIT-' . $visit->id) . '@' . parse_url(config('app.url', 'localhost'), PHP_URL_HOST);

        return "BEGIN:VCALENDAR\r\n" .
            "VERSION:2.0\r\n" .
            "PRODID:-//VisitorPortal//Appointment Booking//EN\r\n" .
            "CALSCALE:GREGORIAN\r\n" .
            "METHOD:REQUEST\r\n" .
            "BEGIN:VEVENT\r\n" .
            "UID:{$uid}\r\n" .
            "DTSTAMP:{$dtStamp}\r\n" .
            "DTSTART:{$dtStart}\r\n" .
            "DTEND:{$dtEnd}\r\n" .
            "SUMMARY:{$title}\r\n" .
            "DESCRIPTION:{$description}\r\n" .
            "LOCATION:{$location}\r\n" .
            "STATUS:CONFIRMED\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR\r\n";
    }
}
