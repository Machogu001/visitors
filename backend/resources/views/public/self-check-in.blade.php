<x-guest-layout>
    <main class="mx-auto w-full max-w-lg px-4 py-10 sm:py-16">
        <section class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm sm:p-8" aria-labelledby="check-in-heading">
            <h1 id="check-in-heading" class="text-2xl font-bold text-base-content">{{ __('Visitor self check-in') }}</h1>
            <p class="mt-2 text-base-content/70">{{ __('Confirm your arrival for this visit.') }}</p>

            <dl class="mt-6 grid gap-4 text-sm">
                <div><dt class="text-base-content/60">{{ __('Visitor') }}</dt><dd class="font-semibold">{{ trim($participant->first_name.' '.$participant->name) }}</dd></div>
                <div><dt class="text-base-content/60">{{ __('Date and time') }}</dt><dd class="font-semibold">{{ $visit->scheduled_from->format('Y-m-d H:i') }}</dd></div>
                <div><dt class="text-base-content/60">{{ __('Site') }}</dt><dd class="font-semibold">{{ $visit->site?->name }}</dd></div>
                <div><dt class="text-base-content/60">{{ __('Host') }}</dt><dd class="font-semibold">{{ $visit->host?->fullName }}</dd></div>
            </dl>

            @if (session('status'))
                <div class="alert alert-success mt-6" role="status">{{ session('status') }}</div>
            @elseif ($insideWindow)
                <form method="POST" action="{{ request()->fullUrl() }}" class="mt-6">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full">{{ __('Confirm check-in') }}</button>
                </form>
            @else
                <div class="alert mt-6" role="status">{{ __('Self check-in is not available at this time. Please contact reception.') }}</div>
            @endif
        </section>
    </main>
</x-guest-layout>