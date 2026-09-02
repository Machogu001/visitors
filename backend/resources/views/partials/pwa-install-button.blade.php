<div
    x-data="{
        installable: false,
        iosInstructions: false,
        iosDismissed: localStorage.getItem('bp_pwa_ios_dismissed') === '1',
        dismissIos() {
            this.iosDismissed = true;
            localStorage.setItem('bp_pwa_ios_dismissed', '1');
        },
    }"
    x-on:pwa:installable.window="installable = $event.detail.available"
    x-on:pwa:ios-instructions.window="iosInstructions = $event.detail.available"
>
    <button
        type="button"
        x-show="installable"
        style="display: none;"
        x-on:click="window.pwaInstall()"
        class="btn btn-primary btn-sm fixed bottom-4 right-4 z-50 rounded-full shadow-lg"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        {{ __('Install App') }}
    </button>

    <div
        x-show="iosInstructions && !iosDismissed"
        style="display: none;"
        class="alert fixed bottom-4 left-4 right-4 z-50 mx-auto max-w-md items-center gap-3 rounded-2xl shadow-lg sm:left-auto"
    >
        <span class="text-sm">{{ __('Install this app: tap the Share icon, then "Add to Home Screen".') }}</span>
        <button type="button" x-on:click="dismissIos()" class="btn btn-ghost btn-xs">{{ __('Dismiss') }}</button>
    </div>
</div>
