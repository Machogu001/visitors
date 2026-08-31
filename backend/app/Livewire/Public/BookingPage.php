<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Livewire\Public;

use App\Models\Department;
use App\Models\Site;
use App\Models\User;
use App\Models\Visit;
use App\Rules\ValidPhoneNumber;
use App\Services\BookingAvailabilityService;
use App\Services\PublicBookingService;
use App\Support\PhoneNumber;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class BookingPage extends Component
{
    public int $step = 1;

    public string $bookingType = 'department_head';

    public ?int $siteId = null;

    public ?int $departmentId = null;

    public string $purpose = '';

    public string $selectedDate = '';

    public string $selectedTime = '';

    public int $durationMinutes = 30;

    public string $salutation = 'mr';

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $phone = '';

    public string $idNumber = '';

    public string $company = '';

    public string $notes = '';

    // Finance & Cheque Handling
    public string $chequeAction = 'pick_up'; // 'drop_off' or 'pick_up'

    public string $chequeNumber = '';

    public string $chequeAmount = '';

    public string $chequeBank = '';

    public string $chequePayee = '';

    public string $signatureData = '';

    public bool $privacyAccepted = false;

    public ?string $confirmedReference = null;

    public ?int $confirmedVisitId = null;

    public function mount(?string $site = null): void
    {
        $sites = app(BookingAvailabilityService::class)->getBookableSites();

        if ($site) {
            $matchingSite = $sites->firstWhere('slug', $site) ?? $sites->firstWhere('id', (int) $site);
            if ($matchingSite) {
                $this->siteId = $matchingSite->id;
            }
        }

        if (! $this->siteId && $sites->isNotEmpty()) {
            $this->siteId = $sites->first()->id;
        }

        $selectableDates = $this->getSelectableDates();
        if (! empty($selectableDates)) {
            $this->selectedDate = $selectableDates[0]['date'];
        }
    }

    public function selectBookingType(string $type): void
    {
        if (in_array($type, ['department_head', 'general'], true)) {
            $this->bookingType = $type;
            $this->departmentId = null;
            $this->selectedTime = '';
        }
    }

    public function selectSite(int $siteId): void
    {
        $this->siteId = $siteId;
        $this->departmentId = null;
        $this->selectedTime = '';
    }

    public function selectDepartment(int $departmentId): void
    {
        $this->departmentId = $departmentId;
        $this->selectedTime = '';
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->selectedTime = '';
    }

    public function selectTime(string $time): void
    {
        $this->selectedTime = $time;
    }

    public function selectDuration(int $minutes): void
    {
        if (in_array($minutes, [15, 30, 45, 60], true)) {
            $this->durationMinutes = $minutes;
            $this->selectedTime = '';
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->step) {
            $this->step = $step;
        } elseif ($step === $this->step + 1) {
            $this->nextStep();
        }
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'bookingType' => 'required|in:department_head,general',
                'siteId' => 'required|exists:sites,id',
            ]);

            $site = Site::query()->find($this->siteId);
            if ($this->bookingType === 'department_head' && ! $site?->allow_department_booking) {
                $this->bookingType = 'general';
            } elseif ($this->bookingType === 'general' && ! $site?->allow_general_booking) {
                $this->bookingType = 'department_head';
            }

            $this->step = 2;

            return;
        }

        if ($this->step === 2) {
            if ($this->bookingType === 'department_head') {
                $this->validate([
                    'departmentId' => 'required|exists:departments,id',
                ], [
                    'departmentId.required' => __('Please select a department or department head.'),
                ]);
            } else {
                $this->validate([
                    'purpose' => 'required|string|max:255',
                ], [
                    'purpose.required' => __('Please select or enter the occasion of your visit.'),
                ]);
            }

            $this->step = 3;

            return;
        }

        if ($this->step === 3) {
            $this->validate([
                'selectedDate' => 'required|date_format:Y-m-d',
                'selectedTime' => 'required|string',
                'durationMinutes' => 'required|integer|in:15,30,45,60',
            ], [
                'selectedTime.required' => __('Please select an appointment time.'),
            ]);

            $this->step = 4;

            return;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1 && $this->step < 5) {
            $this->step--;
        }
    }

    public function getIsFinanceBookingProperty(): bool
    {
        if ($this->selectedDepartment?->is_finance_department) {
            return true;
        }

        if (stripos($this->purpose, 'cheque') !== false || stripos($this->purpose, 'scheck') !== false || stripos($this->purpose, 'finance') !== false || stripos($this->purpose, 'finanz') !== false) {
            return true;
        }

        if ($this->selectedDepartment && (stripos($this->selectedDepartment->name, 'finance') !== false || stripos($this->selectedDepartment->name, 'finanz') !== false || stripos($this->selectedDepartment->name, 'buchhaltung') !== false)) {
            return true;
        }

        return false;
    }

    public function submitBooking(PublicBookingService $bookingService): void
    {
        $rules = [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => ['required', 'string', 'max:50', new ValidPhoneNumber],
            'company' => 'required|string|max:255',
            'salutation' => 'required|in:mr,ms,not_specified',
            'notes' => 'required|string|max:1000',
            'privacyAccepted' => 'accepted',
        ];

        $messages = [
            'firstName.required' => __('Please enter your first name.'),
            'lastName.required' => __('Please enter your last name.'),
            'email.required' => __('Please enter a valid email address.'),
            'email.email' => __('The email address is invalid.'),
            'phone.required' => __('Phone number is required.'),
            'company.required' => __('Please enter your company or organization name.'),
            'notes.required' => __('Please enter the topic or message for the host.'),
            'privacyAccepted.accepted' => __('Please accept the data protection notice.'),
        ];

        if ($this->isFinanceBooking) {
            // For finance department visits, also require ID number upfront
            $rules['idNumber'] = 'required|string|max:100';
            $rules['chequeAction'] = 'required|in:drop_off,pick_up';
            $messages['idNumber.required'] = __('ID number is required for finance department visits.');

            if ($this->chequeAction === 'drop_off') {
                $rules['chequeNumber'] = 'required|string|max:100';
                $rules['chequeAmount'] = 'required|numeric|min:0.01';
                $rules['chequeBank'] = 'required|string|max:150';
                $rules['chequePayee'] = 'required|string|max:200';
                $rules['signatureData'] = 'required|string';

                $messages['chequeNumber.required'] = __('Please enter the cheque number.');
                $messages['chequeAmount.required'] = __('Please enter the cheque amount.');
                $messages['chequeBank.required'] = __('Please enter the bank name.');
                $messages['chequePayee.required'] = __('Please enter the drawer or account name.');
                $messages['signatureData.required'] = __('Please sign to acknowledge the cheque details.');
            }
        }

        $this->validate($rules, $messages);

        $normalizedPhone = PhoneNumber::normalize($this->phone);

        $visit = $bookingService->createBooking([
            'site_id' => (int) $this->siteId,
            'booking_type' => $this->bookingType,
            'department_id' => $this->departmentId ? (int) $this->departmentId : null,
            'purpose' => $this->purpose ?: null,
            'date' => $this->selectedDate,
            'time' => $this->selectedTime,
            'duration_minutes' => $this->durationMinutes,
            'salutation' => $this->salutation,
            'first_name' => $this->firstName,
            'name' => $this->lastName,
            'email' => $this->email,
            'phone' => $normalizedPhone,
            'id_number' => trim($this->idNumber) ?: null,
            'company' => $this->company,
            'notes' => $this->notes,
            'cheque_action' => $this->isFinanceBooking ? $this->chequeAction : null,
            'cheque_number' => $this->chequeAction === 'drop_off' ? trim($this->chequeNumber) : null,
            'cheque_amount' => $this->chequeAction === 'drop_off' ? $this->chequeAmount : null,
            'cheque_bank' => $this->chequeAction === 'drop_off' ? trim($this->chequeBank) : null,
            'cheque_payee_or_drawer' => $this->chequeAction === 'drop_off' ? trim($this->chequePayee) : null,
            'signature_data' => $this->chequeAction === 'drop_off' ? $this->signatureData : null,
            'signed_by_name' => $this->chequeAction === 'drop_off' ? trim($this->firstName.' '.$this->lastName) : null,
        ]);

        $this->confirmedReference = $visit->booking_reference;
        $this->confirmedVisitId = $visit->id;
        $this->step = 5;
    }

    public function resetBooking(): void
    {
        $this->reset([
            'step',
            'bookingType',
            'departmentId',
            'purpose',
            'selectedTime',
            'firstName',
            'lastName',
            'email',
            'phone',
            'company',
            'notes',
            'chequeAction',
            'chequeNumber',
            'chequeAmount',
            'chequeBank',
            'chequePayee',
            'signatureData',
            'privacyAccepted',
            'confirmedReference',
            'confirmedVisitId',
        ]);
        $this->step = 1;
        $this->mount();
    }

    /**
     * @return Collection<int, Site>
     */
    public function getSitesProperty(): Collection
    {
        return app(BookingAvailabilityService::class)->getBookableSites();
    }

    public function getSelectedSiteProperty(): ?Site
    {
        return $this->sites->firstWhere('id', (int) $this->siteId);
    }

    /**
     * @return Collection<int, Department>
     */
    public function getDepartmentsProperty(): Collection
    {
        if (! $this->siteId) {
            return collect();
        }

        return app(BookingAvailabilityService::class)->getBookableDepartmentsForSite((int) $this->siteId);
    }

    public function getSelectedDepartmentProperty(): ?Department
    {
        return $this->departments->firstWhere('id', (int) $this->departmentId);
    }

    public function getSelectedHostProperty(): ?User
    {
        $availabilityService = app(BookingAvailabilityService::class);

        if ($this->bookingType === 'department_head' && $this->selectedDepartment) {
            return $availabilityService->resolveDepartmentHost($this->selectedDepartment);
        }

        if ($this->selectedSite) {
            return $availabilityService->resolveGeneralBookingHost($this->selectedSite);
        }

        return null;
    }

    /**
     * @return array<int, array{date: string, label: string, day_name: string}>
     */
    public function getSelectableDates(): array
    {
        $timezone = $this->selectedSite?->timezone ?: config('app.timezone', 'Africa/Nairobi');

        return app(BookingAvailabilityService::class)->getSelectableDates($timezone);
    }

    /**
     * @return array<int, array{time: string, label: string, available: bool}>
     */
    public function getAvailableSlotsProperty(): array
    {
        if (! $this->selectedSite || ! $this->selectedDate) {
            return [];
        }

        $host = $this->selectedHost;
        if (! $host) {
            return [];
        }

        return app(BookingAvailabilityService::class)->getAvailableSlotsForHost(
            $host,
            $this->selectedSite,
            $this->selectedDate,
            $this->durationMinutes
        );
    }

    public function getConfirmedVisitProperty(): ?Visit
    {
        if (! $this->confirmedVisitId) {
            return null;
        }

        return Visit::query()
            ->with(['site', 'department', 'host', 'visitors'])
            ->find($this->confirmedVisitId);
    }

    public function render(): View
    {
        return view('livewire.public.booking-page', [
            'sites' => $this->sites,
            'selectedSite' => $this->selectedSite,
            'departments' => $this->departments,
            'selectedDepartment' => $this->selectedDepartment,
            'selectedHost' => $this->selectedHost,
            'selectableDates' => $this->getSelectableDates(),
            'availableSlots' => $this->availableSlots,
            'confirmedVisit' => $this->confirmedVisit,
        ])->layout('layouts.guest', [
            'title' => __('Besuchstermin buchen') . ' | ' . config('branding.name', 'VisitorPortal'),
            'maxWidth' => 'max-w-4xl',
        ]);
    }
}
