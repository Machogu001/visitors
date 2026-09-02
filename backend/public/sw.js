const CACHE_NAME = 'visitorportal-static-v1';
const STATIC_ASSET_PATTERN = /\.(?:css|js|png|jpg|jpeg|svg|webp|ico|woff2?)$/;
const EXCLUDED_PATH_PREFIXES = ['/livewire', '/admin', '/portal', '/reception', '/monitor', '/storage'];

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (EXCLUDED_PATH_PREFIXES.some((prefix) => url.pathname.startsWith(prefix))) {
        return;
    }

    const isStaticAsset = url.pathname.startsWith('/build/') || STATIC_ASSET_PATTERN.test(url.pathname);

    event.respondWith(
        fetch(request)
            .then((response) => {
                if (isStaticAsset && response.ok) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                }

                return response;
            })
            .catch(() => caches.match(request))
    );
});
