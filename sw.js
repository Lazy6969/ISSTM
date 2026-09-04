const CACHE_VERSION = 'isstm-v1';
const STATIC_CACHE = CACHE_VERSION + '-static';
const OFFLINE_URL = new URL('offline.html', self.registration.scope).href;

const PRECACHE_ASSETS = [
    OFFLINE_URL,
    new URL('images/logo-isstm.jpg', self.registration.scope).href,
    new URL('images/icon-192.png', self.registration.scope).href,
    new URL('images/icon-512.png', self.registration.scope).href,
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== STATIC_CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

function isStaticAsset(url) {
    return /\.(css|js|png|jpg|jpeg|svg|webp|gif|ico|woff2?|ttf)$/i.test(url.pathname);
}

// Les pages PHP (contenu dynamique, souvent lié à la session) ne sont volontairement jamais mises
// en cache : seul un repli hors-ligne statique (offline.html) est servi en cas d'échec réseau sur
// une navigation. Seuls les fichiers vraiment statiques (CSS/JS/images/polices) sont mis en cache,
// en stale-while-revalidate (réponse immédiate depuis le cache si dispo, puis rafraîchissement en
// arrière-plan) pour rester rapides sans jamais servir de contenu périmé trop longtemps.
self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(req).then((cached) => {
                const networkFetch = fetch(req).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(STATIC_CACHE).then((cache) => cache.put(req, clone));
                    }
                    return response;
                }).catch(() => cached);
                return cached || networkFetch;
            })
        );
    }
});
