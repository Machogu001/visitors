let deferredInstallPrompt = null;
window.pwaInstallState = window.pwaInstallState || {
    installable: false,
    iosInstructions: false,
    installed: false,
    checking: true,
};

const setVisible = (selector, visible) => {
    document.querySelectorAll(selector).forEach((element) => {
        element.style.display = visible ? '' : 'none';
    });
};

const syncInstallUi = () => {
    const state = window.pwaInstallState;
    const installDismissed = sessionStorage.getItem('bp_pwa_install_dismissed') === '1';
    const iosDismissed = localStorage.getItem('bp_pwa_ios_dismissed') === '1';

    setVisible('[data-pwa-installed]', state.installed);
    setVisible('[data-pwa-installable]', state.installable && !state.installed);
    setVisible('[data-pwa-install-banner]', state.installable && !state.installed && !installDismissed);
    setVisible('[data-pwa-ios]', state.iosInstructions && !state.installed);
    setVisible('[data-pwa-ios-banner]', state.iosInstructions && !state.installed && !iosDismissed);
    setVisible('[data-pwa-fallback]', !state.checking && !state.installable && !state.iosInstructions && !state.installed);
};

const updateInstallState = (state) => {
    Object.assign(window.pwaInstallState, state);
    syncInstallUi();
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
    document.querySelectorAll('[data-pwa-install]').forEach((button) => {
        button.addEventListener('click', () => window.pwaInstall());
    });
    document.querySelectorAll('[data-pwa-dismiss-install]').forEach((button) => {
        button.addEventListener('click', () => {
            sessionStorage.setItem('bp_pwa_install_dismissed', '1');
            syncInstallUi();
        });
    });
    document.querySelectorAll('[data-pwa-dismiss-ios]').forEach((button) => {
        button.addEventListener('click', () => {
            localStorage.setItem('bp_pwa_ios_dismissed', '1');
            syncInstallUi();
        });
    });

    updateInstallState({ installed: isStandaloneDisplay() });
    window.setTimeout(() => updateInstallState({ checking: false }), 1200);

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        updateInstallState({ installable: true });
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        updateInstallState({ installable: false, iosInstructions: false, installed: true });
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
