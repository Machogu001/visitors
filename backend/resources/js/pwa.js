let deferredInstallPrompt = null;

const isStandaloneDisplay = () => window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;

const isIos = () => /iphone|ipad|ipod/i.test(window.navigator.userAgent);

export const registerServiceWorker = () => {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Registration failures should never block the app from working.
        });
    });
};

export const initInstallPrompt = () => {
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        window.dispatchEvent(new CustomEvent('pwa:installable', { detail: { available: true } }));
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        window.dispatchEvent(new CustomEvent('pwa:installable', { detail: { available: false } }));
    });

    if (isIos() && !isStandaloneDisplay()) {
        window.dispatchEvent(new CustomEvent('pwa:ios-instructions', { detail: { available: true } }));
    }
};

window.pwaInstall = async () => {
    if (!deferredInstallPrompt) {
        return;
    }

    deferredInstallPrompt.prompt();
    await deferredInstallPrompt.userChoice;
    deferredInstallPrompt = null;
    window.dispatchEvent(new CustomEvent('pwa:installable', { detail: { available: false } }));
};
