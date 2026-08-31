@php
    $brandName = config('branding.name', 'VisitorPortal');
    $logoLightPath = config('branding.logo_light');
    $logoDarkPath = config('branding.logo_dark');
    $hasLogoLight = is_string($logoLightPath) && $logoLightPath !== '' && file_exists(public_path($logoLightPath));
    $hasLogoDark = is_string($logoDarkPath) && $logoDarkPath !== '' && file_exists(public_path($logoDarkPath));
@endphp

<div class="w-full max-w-3xl mx-auto my-4">
    <div class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-xl">
        {{-- Header --}}
        <div class="border-b border-base-300/70 bg-base-100 px-6 py-6 sm:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if ($hasLogoLight || $hasLogoDark)
                        <div class="flex-shrink-0">
                            @if ($hasLogoLight)
                                <img src="{{ asset($logoLightPath) }}" alt="{{ $brandName }}" class="logo-light max-h-12 w-auto object-contain">
                            @endif
                            @if ($hasLogoDark)
                                <img src="{{ asset($logoDarkPath) }}" alt="{{ $brandName }}" class="logo-dark max-h-12 w-auto object-contain">
                            @endif
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-base-content sm:text-3xl">
                            {{ __('Book an appointment') }}
                        </h1>
                        <p class="text-sm text-base-content/70">
                            {{ __('Schedule an appointment with our department heads or at reception.') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm rounded-xl text-base-content/70">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        {{ __('Login') }}
                    </a>
                </div>
            </div>

            {{-- Step Indicator --}}
            @if ($step < 5)
                <div class="mt-6">
                    <ul class="steps steps-horizontal w-full text-xs sm:text-sm">
                        <li class="step {{ $step >= 1 ? 'step-primary' : '' }} cursor-pointer" wire:click="goToStep(1)">
                            {{ __('Appointment type') }}
                        </li>
                        <li class="step {{ $step >= 2 ? 'step-primary' : '' }} cursor-pointer" wire:click="goToStep(2)">
                            {{ $bookingType === 'department_head' ? __('Department') : __('Purpose') }}
                        </li>
                        <li class="step {{ $step >= 3 ? 'step-primary' : '' }} cursor-pointer" wire:click="goToStep(3)">
                            {{ __('Date & Time') }}
                        </li>
                        <li class="step {{ $step >= 4 ? 'step-primary' : '' }} cursor-pointer" wire:click="goToStep(4)">
                            {{ __('Your Details') }}
                        </li>
                    </ul>
                </div>
            @endif
        </div>

        <div class="p-6 sm:p-8">
            {{-- STEP 1: Booking Type & Site --}}
            @if ($step === 1)
                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-semibold mb-1">{{ __('1. What type of appointment would you like to schedule?') }}</h2>
                        <p class="text-sm text-base-content/70">{{ __('Choose between a targeted meeting with a department head or a general visit.') }}</p>
                    </div>

                    @if ($sites->count() > 1)
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ __('Select site') }}</label>
                            <select
                                wire:model.live="siteId"
                                class="select select-bordered w-full rounded-xl"
                            >
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }} ({{ $site->address ?: $site->timezone }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            wire:click="selectBookingType('department_head')"
                            class="card border-2 cursor-pointer transition-all p-5 rounded-2xl {{ $bookingType === 'department_head' ? 'border-primary bg-primary/5 shadow-md ring-2 ring-primary/20' : 'border-base-300 hover:border-base-content/30 bg-base-100' }}"
                        >
                            <div class="flex items-start gap-4">
                                <div class="p-3 rounded-2xl {{ $bookingType === 'department_head' ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold">{{ __('Meeting with Department Head') }}</h3>
                                    <p class="text-sm text-base-content/70 mt-1">
                                        {{ __('Meet specifically with the head of a department (e.g. HR, IT, Procurement, Management).') }}
                                    </p>
                                    @if ($selectedSite)
                                        <div class="mt-3 inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-base-200">
                                            {{ $departments->count() }} {{ __('departments available') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div
                            wire:click="selectBookingType('general')"
                            class="card border-2 cursor-pointer transition-all p-5 rounded-2xl {{ $bookingType === 'general' ? 'border-primary bg-primary/5 shadow-md ring-2 ring-primary/20' : 'border-base-300 hover:border-base-content/30 bg-base-100' }}"
                        >
                            <div class="flex items-start gap-4">
                                <div class="p-3 rounded-2xl {{ $bookingType === 'general' ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold">{{ __('General visit appointment') }}</h3>
                                    <p class="text-sm text-base-content/70 mt-1">
                                        {{ __('For general inquiries, deliveries, facility visits, site tours, or initial discussions at reception.') }}
                                    </p>
                                    <div class="mt-3 inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-base-200">
                                        {{ __('Reception / Front Desk') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button
                            type="button"
                            wire:click="nextStep"
                            class="btn btn-primary h-12 px-8 rounded-xl font-medium"
                        >
                            {{ __('Next') }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- STEP 2: Department Selection or Purpose --}}
            @if ($step === 2)
                <div class="space-y-6">
                    @if ($bookingType === 'department_head')
                        <div>
                            <h2 class="text-xl font-semibold mb-1">{{ __('2. Select the desired department / head') }}</h2>
                            <p class="text-sm text-base-content/70">{{ __('Location:') }} <span class="font-semibold">{{ $selectedSite?->name }}</span></p>
                        </div>

                        @error('departmentId')
                            <div class="alert alert-error text-sm rounded-xl py-2.5">
                                {{ $message }}
                            </div>
                        @enderror

                        @if ($departments->isEmpty())
                            <div class="p-6 text-center border border-dashed border-base-300 rounded-2xl">
                                <p class="text-base-content/70">{{ __('Currently no bookable departments are registered for this site.') }}</p>
                                <button type="button" wire:click="selectBookingType('general')" class="btn btn-primary btn-sm mt-3 rounded-lg">
                                    {{ __('Switch to general appointment') }}
                                </button>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($departments as $dept)
                                    <div
                                        wire:click="selectDepartment({{ $dept->id }})"
                                        class="card border-2 cursor-pointer transition-all p-4 rounded-2xl {{ $departmentId == $dept->id ? 'border-primary bg-primary/5 shadow-md ring-2 ring-primary/20' : 'border-base-300 hover:border-base-content/30 bg-base-100' }}"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <h3 class="font-bold text-base text-base-content">{{ $dept->name }}</h3>
                                                @if ($dept->description)
                                                    <p class="text-xs text-base-content/70 mt-1">{{ $dept->description }}</p>
                                                @endif
                                                @if ($dept->location)
                                                    <div class="text-xs text-base-content/60 mt-1 flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        {{ $dept->location }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="p-2 rounded-xl {{ $departmentId == $dept->id ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <div class="divider my-2"></div>

                                        <div class="flex items-center gap-2.5">
                                            <div class="avatar placeholder">
                                                <div class="bg-primary/20 text-primary rounded-full w-8 h-8 text-xs font-bold">
                                                    {{ $dept->headUser ? strtoupper(substr($dept->headUser->first_name ?? '', 0, 1) . substr($dept->headUser->name ?? '', 0, 1)) : 'HD' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium text-base-content">
                                                    {{ $dept->headUser ? $dept->headUser->fullName : __('Department Head') }}
                                                </div>
                                                <div class="text-[11px] text-base-content/60">
                                                    {{ $dept->headUser?->title ?: __('Head / Lead') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div>
                            <h2 class="text-xl font-semibold mb-1">{{ __('2. What is the occasion of your visit?') }}</h2>
                            <p class="text-sm text-base-content/70">{{ __('Location:') }} <span class="font-semibold">{{ $selectedSite?->name }}</span></p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    {{ __('Occasion / Category') }} <span style="color: #dc2626; font-weight: 800; font-size: 1.125rem; line-height: 1;" aria-hidden="true">*</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @php
                                        $purposes = [
                                            __('Cheque Collection / Drop-off (Finance)'),
                                            __('General consultation & information'),
                                            __('Supplier & service provider appointment'),
                                            __('Technician / Facility service'),
                                            __('Job interview'),
                                            __('Site inspection / Guided tour'),
                                            __('Other request'),
                                        ];
                                    @endphp
                                    @foreach ($purposes as $item)
                                        <button
                                            type="button"
                                            wire:click="$set('purpose', '{{ $item }}')"
                                            class="p-3 text-left border-2 rounded-xl text-sm transition-all {{ $purpose === $item ? 'border-primary bg-primary/5 font-semibold text-primary' : 'border-base-300 hover:border-base-content/30 bg-base-100' }}"
                                        >
                                            {{ $item }}
                                        </button>
                                    @endforeach
                                </div>
                                @error('purpose') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="custom_purpose" class="block text-sm font-medium mb-1">{{ __('Or custom reason') }}</label>
                                <input
                                    id="custom_purpose"
                                    type="text"
                                    wire:model="purpose"
                                    placeholder="{{ __('e.g. Project Discussion') }}"
                                    class="input input-bordered h-12 w-full rounded-xl"
                                >
                            </div>

                            @if ($selectedHost)
                                <div class="p-4 bg-base-200/50 rounded-2xl border border-base-300 flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary/20 text-primary rounded-full w-10 h-10 font-bold">
                                            {{ strtoupper(substr($selectedHost->first_name ?? '', 0, 1) . substr($selectedHost->name ?? '', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold">{{ __('Your contact person / Reception:') }}</div>
                                        <div class="text-sm text-base-content/80">{{ $selectedHost->fullName }} {{ $selectedHost->title ? '('.$selectedHost->title.')' : '' }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center justify-between pt-4">
                        <button
                            type="button"
                            wire:click="previousStep"
                            class="btn btn-ghost h-12 px-6 rounded-xl font-medium"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            {{ __('Back') }}
                        </button>
                        <button
                            type="button"
                            wire:click="nextStep"
                            class="btn btn-primary h-12 px-8 rounded-xl font-medium"
                        >
                            {{ __('Next') }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- STEP 3: Date, Duration & Time Slot --}}
            @if ($step === 3)
                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-semibold mb-1">{{ __('3. When should the appointment take place?') }}</h2>
                        <p class="text-sm text-base-content/70">
                            {{ __('Contact person:') }}
                            <span class="font-semibold text-primary">
                                {{ $selectedHost ? $selectedHost->fullName : ($selectedDepartment ? $selectedDepartment->name : __('Reception')) }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">{{ __('Planned duration of the meeting') }}</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ([15, 30, 45, 60] as $dur)
                                <button
                                    type="button"
                                    wire:click="selectDuration({{ $dur }})"
                                    class="btn btn-sm rounded-xl {{ $durationMinutes === $dur ? 'btn-primary' : 'btn-outline border-base-300' }}"
                                >
                                    {{ $dur }} {{ __('minutes') }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">{{ __('Select date') }}</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2">
                            @foreach ($selectableDates as $item)
                                <button
                                    type="button"
                                    wire:click="selectDate('{{ $item['date'] }}')"
                                    class="p-2.5 rounded-xl border-2 text-center transition-all flex flex-col items-center justify-center {{ $selectedDate === $item['date'] ? 'border-primary bg-primary text-primary-content shadow-md' : 'border-base-300 bg-base-100 hover:border-base-content/30' }}"
                                >
                                    <span class="text-[11px] uppercase font-semibold {{ $selectedDate === $item['date'] ? 'text-primary-content/80' : 'text-base-content/60' }}">{{ $item['day_name'] }}</span>
                                    <span class="text-sm font-bold mt-0.5">{{ \Illuminate\Support\Carbon::parse($item['date'])->format('d.m.') }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium">{{ __('Available times on') }} {{ \Illuminate\Support\Carbon::parse($selectedDate)->isoFormat('dddd, DD. MMMM YYYY') }}</label>
                            @if ($selectedTime)
                                <span class="badge badge-primary badge-sm font-semibold">{{ __('Selected:') }} {{ $selectedTime }}</span>
                            @endif
                        </div>

                        @error('selectedTime')
                            <div class="alert alert-error text-sm rounded-xl py-2 mb-3">
                                {{ $message }}
                            </div>
                        @enderror

                        @if (empty($availableSlots))
                            <div class="p-6 text-center border border-dashed border-base-300 rounded-2xl bg-base-200/30">
                                <p class="text-sm text-base-content/70">{{ __('Unfortunately, no free appointment slots are available for this day. Please choose another date.') }}</p>
                            </div>
                        @else
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2.5 max-h-64 overflow-y-auto p-1">
                                @foreach ($availableSlots as $slot)
                                    <button
                                        type="button"
                                        @if ($slot['available'])
                                            wire:click="selectTime('{{ $slot['time'] }}')"
                                        @endif
                                        @disabled(! $slot['available'])
                                        class="py-2 px-2 rounded-xl text-xs font-semibold text-center border transition-all {{ $selectedTime === $slot['time'] ? 'bg-primary text-primary-content border-primary shadow-md' : ($slot['available'] ? 'border-base-300 hover:border-primary hover:bg-primary/5 bg-base-100 text-base-content' : 'border-base-200 bg-base-200/50 text-base-content/30 cursor-not-allowed line-through') }}"
                                    >
                                        {{ $slot['time'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <button
                            type="button"
                            wire:click="previousStep"
                            class="btn btn-ghost h-12 px-6 rounded-xl font-medium"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            {{ __('Back') }}
                        </button>
                        <button
                            type="button"
                            wire:click="nextStep"
                            @disabled(empty($selectedTime))
                            class="btn btn-primary h-12 px-8 rounded-xl font-medium"
                        >
                            {{ __('Next') }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- STEP 4: Visitor Contact Information --}}
            @if ($step === 4)
                <form wire:submit="submitBooking" class="space-y-6">
                    <div>
                        <h2 class="text-xl font-semibold mb-1">{{ __('4. Enter your contact details') }}</h2>
                        <p class="text-sm text-base-content/70">{{ __('Enter your details so we can announce your visit at reception and to your host.') }}</p>
                    </div>

                    <div class="p-4 bg-primary/5 border border-primary/20 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="text-xs uppercase tracking-wider font-bold text-primary">{{ __('Selected appointment') }}</div>
                            <div class="text-sm font-semibold text-base-content mt-0.5">
                                {{ \Illuminate\Support\Carbon::parse($selectedDate)->isoFormat('dddd, DD.MM.YYYY') }} • {{ $selectedTime }} ({{ $durationMinutes }} min)
                            </div>
                            <div class="text-xs text-base-content/70">
                                {{ $selectedSite?->name }} • {{ $bookingType === 'department_head' && $selectedDepartment ? $selectedDepartment->name : ($purpose ?: __('General visit')) }}
                            </div>
                        </div>
                        <button type="button" wire:click="goToStep(3)" class="btn btn-ghost btn-xs text-primary self-start sm:self-center">
                            {{ __('Change') }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('Salutation') }}</label>
                            <select wire:model="salutation" class="select select-bordered h-12 w-full rounded-xl">
                                <option value="mr">{{ __('Mr.') }}</option>
                                <option value="ms">{{ __('Ms.') }}</option>
                                <option value="not_specified">{{ __('Not specified') }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="first_name" class="block text-sm font-medium mb-1">
                                {{ __('First name') }} <span class="text-error">*</span>
                            </label>
                            <input
                                id="first_name"
                                type="text"
                                wire:model="firstName"
                                placeholder="{{ __('John') }}"
                                class="input input-bordered h-12 w-full rounded-xl"
                            >
                            @error('firstName') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium mb-1">
                                {{ __('Last name') }} <span class="text-error">*</span>
                            </label>
                            <input
                                id="last_name"
                                type="text"
                                wire:model="lastName"
                                placeholder="{{ __('Doe') }}"
                                class="input input-bordered h-12 w-full rounded-xl"
                            >
                            @error('lastName') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-medium mb-1">
                                {{ __('Email') }} <span class="text-error">*</span>
                            </label>
                            <input
                                id="email"
                                type="email"
                                wire:model="email"
                                placeholder="{{ __('john.doe@example.com') }}"
                                class="input input-bordered h-12 w-full rounded-xl"
                            >
                            @error('email') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium mb-1">
                                {{ __('Phone number') }} <span style="color: #dc2626; font-weight: 800; font-size: 1.125rem; line-height: 1;" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="phone"
                                type="tel"
                                wire:model="phone"
                                placeholder="{{ __('+254 700 000000 or 0700 000000') }}"
                                class="input input-bordered h-12 w-full rounded-xl"
                                required
                            >
                            <p class="text-[11px] text-base-content/60 mt-1">{{ __('Local (07... / 01...) or international (+254...) formats.') }}</p>
                            @error('phone') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="company" class="block text-sm font-medium mb-1">
                            {{ __('Company / Organization') }} <span style="color: #dc2626; font-weight: 800; font-size: 1.125rem; line-height: 1;" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="company"
                            type="text"
                            wire:model="company"
                            placeholder="{{ __('Acme Corp') }}"
                            class="input input-bordered h-12 w-full rounded-xl"
                            required
                        >
                        @error('company') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium mb-1">
                            {{ __('Topic / Message to the host') }} <span class="text-red-600 font-bold text-base" aria-hidden="true">*</span>
                        </label>
                        <textarea
                            id="notes"
                            wire:model="notes"
                            rows="3"
                            placeholder="{{ __('What is the meeting about or are there any special requirements?') }}"
                            class="textarea textarea-bordered w-full rounded-xl"
                            required
                        ></textarea>
                        @error('notes') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if ($this->isFinanceBooking)
                        {{-- Finance Cheque Section & Signature --}}
                        <div class="p-5 rounded-2xl border-2 border-primary/30 bg-primary/5 space-y-4">
                            <div class="flex items-center gap-2 border-b border-primary/20 pb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <div>
                                    <h3 class="font-bold text-base text-base-content">{{ __('Finance Department Cheque Acknowledgement') }}</h3>
                                    <p class="text-xs text-base-content/70">{{ __('Please record the cheque details and sign below for verification.') }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">{{ __('Cheque Transaction Type') }} <span class="text-error">*</span></label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all {{ $chequeAction === 'drop_off' ? 'border-primary bg-primary/10 font-semibold' : 'border-base-300 bg-base-100' }}">
                                        <input type="radio" wire:model.live="chequeAction" value="drop_off" class="radio radio-primary radio-sm">
                                        <div>
                                            <div class="text-sm">{{ __('Drop-off Cheque (Submission / Payment)') }}</div>
                                            <div class="text-[11px] text-base-content/60">{{ __('You are submitting a cheque to our Finance Department') }}</div>
                                        </div>
                                    </label>

                                    <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all {{ $chequeAction === 'pick_up' ? 'border-primary bg-primary/10 font-semibold' : 'border-base-300 bg-base-100' }}">
                                        <input type="radio" wire:model.live="chequeAction" value="pick_up" class="radio radio-primary radio-sm">
                                        <div>
                                            <div class="text-sm">{{ __('Pick-up Cheque (Collection)') }}</div>
                                            <div class="text-[11px] text-base-content/60">{{ __('You are collecting a payment cheque from Finance') }}</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">{{ __('Identification & Contact') }} <span class="text-error">*</span></label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label for="idNumber" class="block text-xs font-semibold mb-1">
                                            {{ __('ID Number / Passport') }} <span class="text-error">*</span>
                                        </label>
                                        <input
                                            id="idNumber"
                                            type="text"
                                            wire:model="idNumber"
                                            placeholder="{{ __('e.g., 12345678 or A12345678') }}"
                                            class="input input-bordered h-12 w-full rounded-xl"
                                        >
                                        @error('idNumber') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-xs font-semibold mb-1">
                                            {{ __('Phone Number') }} <span class="text-error">*</span>
                                        </label>
                                        <input
                                            id="phone"
                                            type="tel"
                                            wire:model="phone"
                                            placeholder="{{ __('+254 700 000000 or 0700 000000') }}"
                                            class="input input-bordered h-12 w-full rounded-xl"
                                        >
                                        <p class="text-[11px] text-base-content/60 mt-1">{{ __('Local (07... / 01...) or international (+254...).') }}</p>
                                        @error('phone') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div>
                                    <label for="chequeNumber" class="block text-xs font-semibold mb-1">
                                        {{ __('Cheque Number') }} <span class="text-error">*</span>
                                    </label>
                                    <input
                                        id="chequeNumber"
                                        type="text"
                                        wire:model="chequeNumber"
                                        placeholder="e.g. 000452"
                                        class="input input-bordered h-11 w-full rounded-xl text-sm"
                                    >
                                    @error('chequeNumber') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="chequeAmount" class="block text-xs font-semibold mb-1">
                                        {{ __('Amount (KES)') }} <span class="text-error">*</span>
                                    </label>
                                    <input
                                        id="chequeAmount"
                                        type="number"
                                        step="0.01"
                                        wire:model="chequeAmount"
                                        placeholder="e.g. 45000.00"
                                        class="input input-bordered h-11 w-full rounded-xl text-sm font-mono"
                                    >
                                    @error('chequeAmount') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="chequeBank" class="block text-xs font-semibold mb-1">
                                        {{ __('Bank Name') }} <span class="text-error">*</span>
                                    </label>
                                    <input
                                        id="chequeBank"
                                        type="text"
                                        wire:model="chequeBank"
                                        placeholder="e.g. Equity Bank, KCB, NCBA"
                                        class="input input-bordered h-11 w-full rounded-xl text-sm"
                                    >
                                    @error('chequeBank') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="chequePayee" class="block text-xs font-semibold mb-1">
                                    {{ $chequeAction === 'pick_up' ? __('Cheque Payee / Beneficiary Name') : __('Drawer / Account Name') }}
                                </label>
                                <input
                                    id="chequePayee"
                                    type="text"
                                    wire:model="chequePayee"
                                    placeholder="e.g. John Doe / Company Name"
                                    class="input input-bordered h-11 w-full rounded-xl text-sm"
                                >
                            </div>

                            {{-- Digital Signature Pad --}}
                            <div
                                x-data="{
                                    isDrawing: false,
                                    canvas: null,
                                    ctx: null,
                                    hasSignature: false,
                                    init() {
                                        this.canvas = this.$refs.sigCanvas;
                                        this.ctx = this.canvas.getContext('2d');
                                        this.ctx.strokeStyle = '#1e293b';
                                        this.ctx.lineWidth = 2.5;
                                        this.ctx.lineCap = 'round';
                                    },
                                    startDraw(e) {
                                        this.isDrawing = true;
                                        const rect = this.canvas.getBoundingClientRect();
                                        const x = (e.clientX || e.touches[0].clientX) - rect.left;
                                        const y = (e.clientY || e.touches[0].clientY) - rect.top;
                                        this.ctx.beginPath();
                                        this.ctx.moveTo(x, y);
                                    },
                                    draw(e) {
                                        if (!this.isDrawing) return;
                                        e.preventDefault();
                                        const rect = this.canvas.getBoundingClientRect();
                                        const x = (e.clientX || (e.touches && e.touches[0].clientX)) - rect.left;
                                        const y = (e.clientY || (e.touches && e.touches[0].clientY)) - rect.top;
                                        this.ctx.lineTo(x, y);
                                        this.ctx.stroke();
                                        this.hasSignature = true;
                                    },
                                    stopDraw() {
                                        if (!this.isDrawing) return;
                                        this.isDrawing = false;
                                        if (this.hasSignature) {
                                            const dataUrl = this.canvas.toDataURL('image/png');
                                            $wire.set('signatureData', dataUrl);
                                        }
                                    },
                                    clear() {
                                        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                                        this.hasSignature = false;
                                        $wire.set('signatureData', '');
                                    }
                                }"
                                class="space-y-1.5"
                            >
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-semibold">
                                        {{ __('Visitor Signature') }} <span class="text-error">*</span>
                                    </label>
                                    <button
                                        type="button"
                                        @click="clear()"
                                        class="btn btn-ghost btn-xs text-error font-semibold"
                                    >
                                        {{ __('Clear Signature') }}
                                    </button>
                                </div>

                                <div class="relative border-2 border-dashed border-primary/40 rounded-xl bg-base-100 overflow-hidden touch-none">
                                    <canvas
                                        x-ref="sigCanvas"
                                        width="600"
                                        height="140"
                                        class="w-full h-32 block cursor-crosshair"
                                        @mousedown="startDraw($event)"
                                        @mousemove="draw($event)"
                                        @mouseup="stopDraw()"
                                        @mouseleave="stopDraw()"
                                        @touchstart="startDraw($event)"
                                        @touchmove="draw($event)"
                                        @touchend="stopDraw()"
                                    ></canvas>
                                    <div x-show="!hasSignature" class="pointer-events-none absolute inset-0 flex items-center justify-center text-xs text-base-content/40 font-medium">
                                        {{ __('✍️ Sign here with your finger or mouse to acknowledge cheque details') }}
                                    </div>
                                </div>
                                @error('signatureData') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    <div class="pt-2">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="privacyAccepted"
                                class="checkbox checkbox-primary mt-0.5 rounded-lg"
                            >
                            <span class="text-xs text-base-content/80 leading-relaxed">
                                {{ __('I agree that my contact details will be processed for conducting and documenting the visit in accordance with the privacy policy.') }}
                            </span>
                        </label>
                        @error('privacyAccepted') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <button
                            type="button"
                            wire:click="previousStep"
                            class="btn btn-ghost h-12 px-6 rounded-xl font-medium"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            {{ __('Back') }}
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="btn btn-primary h-12 px-8 rounded-xl font-medium"
                        >
                            <span wire:loading.remove>{{ __('Book appointment now') }}</span>
                            <span wire:loading class="flex items-center gap-2">
                                <span class="loading loading-spinner loading-sm"></span>
                                {{ __('Booking in progress...') }}
                            </span>
                        </button>
                    </div>
                </form>
            @endif

            {{-- STEP 5: Booking Confirmation Screen --}}
            @if ($step === 5)
                <div class="space-y-6 text-center py-4">
                    <div class="inline-flex p-4 rounded-full bg-success/10 text-success mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-base-content sm:text-3xl">
                            {{ __('Your appointment was successfully booked!') }}
                        </h2>
                        <p class="text-sm text-base-content/70 mt-2 max-w-md mx-auto">
                            {{ __('A confirmation has been sent to your email address. We look forward to your visit.') }}
                        </p>
                    </div>

                    <div class="inline-block bg-base-200/60 border border-base-300 rounded-2xl px-6 py-4 mx-auto">
                        <div class="text-xs uppercase tracking-wider font-semibold text-base-content/60">{{ __('Your personal booking code') }}</div>
                        <div class="text-3xl font-extrabold tracking-widest text-primary font-mono mt-1">
                            {{ $confirmedReference }}
                        </div>
                        <div class="text-xs text-base-content/50 mt-1">{{ __('Please have this code ready when checking in at reception.') }}</div>
                    </div>

                    @if ($confirmedVisit)
                        <div class="card border border-base-300 bg-base-100 rounded-2xl p-6 text-left max-w-lg mx-auto shadow-sm">
                            <h3 class="font-bold text-base mb-4 text-base-content border-b border-base-200 pb-2 flex items-center justify-between">
                                <span>{{ __('Appointment details') }}</span>
                                @if ($confirmedVisit->status === 'pending_approval')
                                    <span class="badge badge-warning badge-sm font-semibold">{{ __('Pending Host Approval') }}</span>
                                @else
                                    <span class="badge badge-success badge-sm font-semibold">{{ __('Confirmed') }}</span>
                                @endif
                            </h3>

                            @if ($confirmedVisit->status === 'pending_approval')
                                <div class="alert alert-warning/20 border border-warning/40 rounded-xl p-3 mb-4 text-xs">
                                    <div class="font-semibold text-warning-content">{{ __('Approval in progress') }}</div>
                                    <div class="text-base-content/80 mt-0.5">
                                        @if ($confirmedVisit->department?->receptionist)
                                            {{ __('The Host and Department Executive Receptionist have received your request. You will be notified once approved.') }}
                                        @else
                                            {{ __('Your host has received your request. You will receive an email confirmation once approved.') }}
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-start">
                                    <span class="text-base-content/60">{{ __('Date & Time:') }}</span>
                                    <span class="font-semibold text-right">
                                        {{ $confirmedVisit->scheduled_from->isoFormat('dddd, DD.MM.YYYY') }}<br>
                                        {{ $confirmedVisit->scheduled_from->format('H:i') }} - {{ $confirmedVisit->scheduled_until->format('H:i') }}
                                    </span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-base-content/60">{{ __('Location:') }}</span>
                                    <span class="font-semibold text-right">{{ $confirmedVisit->site?->name }} ({{ $confirmedVisit->site?->address ?: 'Main Building' }})</span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-base-content/60">{{ __('Host:') }}</span>
                                    <span class="font-semibold text-right">{{ $confirmedVisit->host?->fullName }}</span>
                                </div>

                                @if ($confirmedVisit->department)
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">{{ __('Department:') }}</span>
                                        <span class="font-semibold text-right">{{ $confirmedVisit->department->name }}</span>
                                    </div>
                                @endif

                                @if ($confirmedVisit->visitors->isNotEmpty())
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">{{ __('Visitor:') }}</span>
                                        <span class="font-semibold text-right">
                                            {{ $confirmedVisit->visitors->first()->first_name }} {{ $confirmedVisit->visitors->first()->name }}
                                        </span>
                                    </div>
                                @endif

                                @if ($confirmedVisit->cheque_number)
                                    <div class="border-t border-base-200 pt-3 mt-3">
                                        <div class="text-xs font-bold uppercase text-primary mb-2">{{ __('Finance Cheque Details') }}</div>
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div>
                                                <span class="text-base-content/60">{{ __('Action:') }}</span>
                                                <span class="font-semibold block">{{ $confirmedVisit->cheque_action === 'pick_up' ? __('Cheque Pick-up') : __('Cheque Drop-off') }}</span>
                                            </div>
                                            <div>
                                                <span class="text-base-content/60">{{ __('Cheque No:') }}</span>
                                                <span class="font-semibold font-mono block">{{ $confirmedVisit->cheque_number }}</span>
                                            </div>
                                            <div>
                                                <span class="text-base-content/60">{{ __('Amount:') }}</span>
                                                <span class="font-semibold font-mono block">KES {{ number_format((float)$confirmedVisit->cheque_amount, 2) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-base-content/60">{{ __('Bank:') }}</span>
                                                <span class="font-semibold block">{{ $confirmedVisit->cheque_bank }}</span>
                                            </div>
                                            @if ($confirmedVisit->signed_at)
                                                <div class="col-span-2 flex items-center gap-1.5 text-success font-medium pt-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    {{ __('Digitally Signed & Acknowledged') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4">
                        @if ($confirmedReference)
                            <a
                                href="{{ route('public.book.ical', ['reference' => $confirmedReference]) }}"
                                class="btn btn-outline border-base-300 rounded-xl w-full sm:w-auto"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ __('Save to calendar (.ics)') }}
                            </a>
                        @endif

                        <button
                            type="button"
                            wire:click="resetBooking"
                            class="btn btn-primary rounded-xl w-full sm:w-auto"
                        >
                            {{ __('Book another appointment') }}
                        </button>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('login') }}" class="text-xs text-base-content/60 hover:text-primary transition-colors">
                            {{ __('Back to login page') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
        </div>
    </div>
</div>
