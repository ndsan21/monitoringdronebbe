const CACHE_NAME = 'logdrone-cache-v3'; // Versi naik ke v3

// Sesuaikan antrean nama file menjadi ekstensi .jpg
const assetsToCache = [
    '/',
    '/manifest.json',
    '/favicon.jpg',
    '/icons/icon-192x192.jpg',
    '/icons/icon-512x512.jpg'
];

// 1. Tahap Install: Amankan aset statis ke memori HP
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(assetsToCache))
            .then(() => self.skipWaiting())
    );
});

// 2. Tahap Aktivasi: Hancurkan sisa cache lama
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Tahap Fetch: Strategi Network First
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request)
            .then(networkResponse => {
                if (networkResponse && networkResponse.status === 200) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                }
                return networkResponse;
            })
            .catch(() => {
                return caches.match(event.request);
            })
    );
});