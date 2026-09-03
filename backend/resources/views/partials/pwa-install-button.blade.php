<div
    x-data="{
        installable: false,
        installDismissed: sessionStorage.getItem('bp_pwa_install_dismissed') === '1',
        iosInstructions: false,
        iosDismissed: localStorage.getItem('bp_pwa_ios_dismissed') === '1',
        dismissInstall() {
            this.installDismissed = true;
            sessionStorage.setItem('bp_pwa_install_dismissed', '1');
        },
        dismissIos() {
            this.iosDismissed = true;
            localStorage.setItem('bp_pwa_ios_dismissed', '1');
        },
    }"
    x-on:pwa:installable.window="installable = $event.detail.available"
    x-on:pwa:ios-instructions.window="iosInstructions = $event.detail.available"
>
    <div
        x-show="installable && !installDismissed"
        style="display: none;"
        class="fixed top-20 right-4 z-[60] flex items-center gap-2 rounded-2xl border border-base-300 bg-base-100 px-4 py-2.5 shadow-lg lg:top-4"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        <span class="text-sm font-medium">{{ __('Install this app for quick access') }}</span>
        <button type="button" x-on:click="window.pwaInstall()" class="btn btn-primary btn-xs rounded-lg">
            {{ __('Install') }}
        </button>
        <button type="button" x-on:click="dismissInstall()" class="btn btn-ghost btn-xs btn-square" aria-label="{{ __('Dismiss') }}">
            &times;
        </button>
    </div>

    <div
        x-show="iosInstructions && !iosDismissed"
        style="display: none;"
        class="fixed top-20 left-4 right-4 z-[60] mx-auto flex max-w-md items-center gap-3 rounded-2xl border border-base-300 bg-base-100 px-4 py-2.5 shadow-lg sm:left-auto lg:top-4"
    >
        <span class="text-sm">{{ __('Install this app: tap the Share icon, then "Add to Home Screen".') }}</span>
        <button type="button" x-on:click="dismissIos()" class="btn btn-ghost btn-xs">{{ __('Dismiss') }}</button>
    </div>
</div>
