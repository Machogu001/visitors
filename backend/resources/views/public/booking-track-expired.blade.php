@php
    $brandName = config('branding.name', 'VisitorPortal');
    $logoLightPath = config('branding.logo_light');
    $logoDarkPath = config('branding.logo_dark');
    $hasLogoLight = is_string($logoLightPath) && $logoLightPath !== '' && file_exists(public_path($logoLightPath));
    $hasLogoDark = is_string($logoDarkPath) && $logoDarkPath !== '' && file_exists(public_path($logoDarkPath));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Track your booking') }} · {{ $brandName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 text-base-content antialiased">
<div class="flex min-h-screen items-center justify-center px-4 py-10">
    <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-base-300 bg-base-100 p-8 text-center shadow-xl">
        @if ($hasLogoLight || $hasLogoDark)
            <div class="mb-6 flex justify-center">
                @if ($hasLogoLight)
                    <img src="{{ asset($logoLightPath) }}" alt="{{ $brandName }}" class="max-h-12 w-auto object-contain">
                @endif
                @if ($hasLogoDark)
                    <img src="{{ asset($logoDarkPath) }}" alt="{{ $brandName }}" class="max-h-12 w-auto object-contain">
                @endif
            </div>
        @endif

        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-base-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <h1 class="text-xl font-bold tracking-tight">{{ __('This tracking link is no longer active') }}</h1>

        <p class="mt-2 text-sm text-base-content/70">
            @if ($reason === 'checked_in')
                {{ __('You have already been checked in for this visit on :datetime.', ['datetime' => \Carbon\Carbon::parse($checkedInAt)->format('d.m.Y H:i')]) }}
            @else
                {{ __('The scheduled time for this appointment has passed.') }}
            @endif
        </p>

        <p class="mt-4 text-xs text-base-content/50">
            {{ __('Booking Code: :code', ['code' => $visit->booking_reference]) }}
        </p>
    </div>
</div>
</body>
</html>
