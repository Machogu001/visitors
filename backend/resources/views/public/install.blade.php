<x-guest-layout title="{{ __('Install :name', ['name' => config('branding.name', 'VisitorPortal')]) }}">
    <main class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-sm">
        <div class="border-b border-base-300/70 px-7 py-7 sm:px-9">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4M5 19h14" />
                </svg>
            </div>
            <h1 class="mt-5 text-3xl font-semibold tracking-tight text-base-content">
                {{ __('Install :name', ['name' => config('branding.name', 'VisitorPortal')]) }}
            </h1>
            <p class="mt-3 text-sm leading-7 text-base-content/70 sm:text-base">
                {{ __('Add the visitor portal to this device for quick access in its own app window.') }}
            </p>
        </div>

        <div class="px-7 py-7 sm:px-9">
            <div data-pwa-installed style="display: none;" class="alert alert-success rounded-2xl">
                <span>{{ __('The app is already installed on this device.') }}</span>
            </div>

            <button
                data-pwa-installable
                data-pwa-install
                style="display: none;"
                type="button"
                class="btn btn-primary h-12 w-full rounded-xl"
            >
                {{ __('Install app') }}
            </button>

            <div data-pwa-ios style="display: none;" class="space-y-3">
                <p class="font-semibold">{{ __('Install on this iPhone or iPad') }}</p>
                <p class="text-sm leading-6 text-base-content/70">
                    {{ __('In Safari, tap the Share button, then select Add to Home Screen.') }}
                </p>
            </div>

            <div data-pwa-fallback style="display: none;" class="space-y-3">
                <p class="font-semibold">{{ __('Install from your browser') }}</p>
                <p class="text-sm leading-6 text-base-content/70">
                    {{ __('Open your browser menu and select Install app or Add to Home screen. Installation requires HTTPS and a supported browser.') }}
                </p>
            </div>

            <div class="mt-7 flex flex-col gap-3 border-t border-base-300/70 pt-6 sm:flex-row">
                <a href="{{ route('login') }}" class="btn btn-outline flex-1 rounded-xl">{{ __('Sign in') }}</a>
                <a href="{{ route('public.book') }}" class="btn btn-ghost flex-1 rounded-xl">{{ __('Book a visit') }}</a>
            </div>
        </div>
    </main>
</x-guest-layout>