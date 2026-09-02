@php
    use App\Enums\VisitStatusEnum;
    use Carbon\Carbon;

    $brandName = config('branding.name', 'VisitorPortal');
    $logoLightPath = config('branding.logo_light');
    $logoDarkPath = config('branding.logo_dark');
    $hasLogoLight = is_string($logoLightPath) && $logoLightPath !== '' && file_exists(public_path($logoLightPath));
    $hasLogoDark = is_string($logoDarkPath) && $logoDarkPath !== '' && file_exists(public_path($logoDarkPath));

    $status = VisitStatusEnum::tryFrom($visit->status);
    $statusBadgeClass = match ($status) {
        VisitStatusEnum::Planned => 'badge-success',
        VisitStatusEnum::PendingApproval => 'badge-warning',
        VisitStatusEnum::Rejected, VisitStatusEnum::Canceled => 'badge-error',
        VisitStatusEnum::Completed => 'badge-neutral',
        default => 'badge-ghost',
    };

    $scheduledFrom = Carbon::parse($visit->scheduled_from);
    $scheduledUntil = Carbon::parse($visit->scheduled_until);
    $visitor = $visit->visitors->first();
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
    <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-xl">
        <div class="border-b border-base-300/70 px-6 py-6 sm:px-8">
            <div class="flex items-center gap-4">
                @if ($hasLogoLight || $hasLogoDark)
                    <div class="flex-shrink-0">
                        @if ($hasLogoLight)
                            <img src="{{ asset($logoLightPath) }}" alt="{{ $brandName }}" class="max-h-12 w-auto object-contain">
                        @endif
                        @if ($hasLogoDark)
                            <img src="{{ asset($logoDarkPath) }}" alt="{{ $brandName }}" class="max-h-12 w-auto object-contain">
                        @endif
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-bold tracking-tight sm:text-2xl">{{ __('Your appointment') }}</h1>
                    <p class="text-sm text-base-content/70">{{ __('Booking Code: :code', ['code' => $visit->booking_reference]) }}</p>
                </div>
            </div>
        </div>

        <div class="space-y-4 p-6 sm:p-8">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-base-content/70">{{ __('Status') }}</span>
                <span class="badge {{ $statusBadgeClass }} font-semibold">{{ $status?->label() ?? $visit->status }}</span>
            </div>

            <div class="flex justify-between text-sm">
                <span class="text-base-content/60">{{ __('Date & Time:') }}</span>
                <span class="font-semibold text-right">{{ $scheduledFrom->isoFormat('dddd, DD.MM.YYYY') }} · {{ $scheduledFrom->format('H:i') }}–{{ $scheduledUntil->format('H:i') }}</span>
            </div>

            <div class="flex justify-between text-sm">
                <span class="text-base-content/60">{{ __('Location:') }}</span>
                <span class="font-semibold text-right">{{ $visit->site?->name }}</span>
            </div>

            <div class="flex justify-between text-sm">
                <span class="text-base-content/60">{{ __('Host:') }}</span>
                <span class="font-semibold text-right">{{ $visit->host?->fullName }}</span>
            </div>

            @if ($visit->department)
                <div class="flex justify-between text-sm">
                    <span class="text-base-content/60">{{ __('Department:') }}</span>
                    <span class="font-semibold text-right">{{ $visit->department->name }}</span>
                </div>
            @endif

            @if ($visitor)
                <div class="flex justify-between text-sm">
                    <span class="text-base-content/60">{{ __('Visitor:') }}</span>
                    <span class="font-semibold text-right">{{ trim($visitor->first_name.' '.$visitor->name) }}</span>
                </div>
            @endif

            <div class="pt-4">
                <a href="{{ route('public.book.ical', $visit->booking_reference) }}" class="btn btn-outline btn-sm w-full rounded-xl">
                    {{ __('Save to calendar (.ics)') }}
                </a>
            </div>

            <p class="pt-2 text-center text-xs text-base-content/50">
                {{ __('This link becomes inactive once your visit time has passed or you have been checked in.') }}
            </p>
        </div>
    </div>
</div>
</body>
</html>
