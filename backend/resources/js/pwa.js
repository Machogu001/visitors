let deferredInstallPrompt = null;
window.pwaInstallState = window.pwaInstallState || {
    installable: false,
    iosInstructions: false,
};

const updateInstallState = (state) => {
    Object.assign(window.pwaInstallState, state);
    window.dispatchEvent(new CustomEvent('pwa:state-changed', { detail: window.pwaInstallState }));
};

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
        updateInstallState({ installable: true });
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        updateInstallState({ installable: false, iosInstructions: false });
    });

    if (isIos() && !isStandaloneDisplay()) {
        updateInstallState({ iosInstructions: true });
    }
};

window.pwaInstall = async () => {
    if (!deferredInstallPrompt) {
        return;
    }

    deferredInstallPrompt.prompt();
    await deferredInstallPrompt.userChoice;
    deferredInstallPrompt = null;
    updateInstallState({ installable: false });
};
