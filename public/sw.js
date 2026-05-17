const CACHE_NAME = 'pwa-cache-v2'; // Ganti v1 ke v2
const STATIC_ASSETS = [
    '/',
    '/offline',
    // '/foto/logo-pwa.svg'  // <-- Comment dulu sementara
];

self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('Cache opened');
            return cache.addAll(STATIC_ASSETS);
        })
    );
});

self.addEventListener('fetch', e => {
    e.respondWith(
        caches.match(e.request).then(res => res || fetch(e.request))
    );
});