self.addEventListener('install', function (event) {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    clients.claim();
});

self.addEventListener('fetch', function (event) {
    // for offline-read simple cache-first for GET
    if (event.request.method !== 'GET') return;
    event.respondWith(fetch(event.request).catch(() => caches.match(event.request)));
});
