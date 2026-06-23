const CACHE_NAME = 'logdrone-cache-v3'; // Versi naik ke v3

// Sesuaikan antrean nama file menjadi ekstensi .jpg
const assetsToCache = [
    '/',
    '/manifest.json',
    '/favicon.jpg',
    '/icons/icon-192x192.jpg',
    '/icons/icon-512x512.jpg'
];

// 1. BUAT/BUKA DATABASE INDEXEDDB
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('LogDroneDB', 1);
        request.onupgradeneeded = function(event) {
            const db = event.target.result;
            // Buat tabel (object store) bernama 'offlineLogs'
            if (!db.objectStoreNames.contains('offlineLogs')) {
                db.createObjectStore('offlineLogs', { keyPath: 'id', autoIncrement: true });
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

// 2. FUNGSI MENYIMPAN DATA KE INDEXEDDB
async function saveToIndexedDB(data) {
    const db = await openDB();
    const tx = db.transaction('offlineLogs', 'readwrite');
    const store = tx.objectStore('offlineLogs');
    store.add(data);
    return tx.complete;
}

// 3. EVENT LISTENER: BACKGROUND SYNC
self.addEventListener('sync', event => {
    if (event.tag === 'sync-flight-logs') {
        event.waitUntil(syncOfflineLogs());
    }
});

// 4. FUNGSI MENGIRIM DATA DARI INDEXEDDB KE API LARAVEL SAAT ONLINE
async function syncOfflineLogs() {
    const db = await openDB();
    const tx = db.transaction('offlineLogs', 'readonly');
    const store = tx.objectStore('offlineLogs');
    const logs = await new Promise((resolve) => {
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result);
    });

    if (logs.length === 0) return;

    // Loop semua data offline dan kirim ke server
    for (const log of logs) {
        try {
            const response = await fetch('/api/sync-flight-logs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(log.data) // Kirim data JSON
            });

            if (response.ok) {
                // Jika sukses terkirim, hapus data dari IndexedDB
                const deleteTx = db.transaction('offlineLogs', 'readwrite');
                deleteTx.objectStore('offlineLogs').delete(log.id);
            }
        } catch (error) {
            console.error('Sync failed for log', log.id, error);
            // Biarkan di IndexedDB, akan dicoba lagi nanti
        }
    }
}

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