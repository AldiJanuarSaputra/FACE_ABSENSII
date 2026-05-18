const CACHE_NAME = 'face-absen-cache-v1';
const STATIC_ASSETS = [
  'index.php',
  'absensi.php',
  'register.php',
  'siswa.php',
  'rekap.php',
  'manifest.json',
  'js/face-api.min.js',
  'models/tiny_face_detector_model-weights_manifest.json',
  'models/tiny_face_detector_model-shard1',
  'models/face_landmark_68_model-weights_manifest.json',
  'models/face_landmark_68_model-shard1',
  'models/face_recognition_model-weights_manifest.json',
  'models/face_recognition_model-shard1',
  'models/face_recognition_model-shard2',
  'icons/icon-192x192.png',
  'icons/icon-512x512.png',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Install Event - cache all static assets and app shell
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('[Service Worker] Caching App Shell and Assets');
      return cache.addAll(STATIC_ASSETS);
    }).then(() => self.skipWaiting())
  );
});

// Activate Event - clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            console.log('[Service Worker] Removing old cache', key);
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Event
self.addEventListener('fetch', event => {
  // Skip POST requests (like API saves)
  if (event.request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(event.request.url);

  // Network-First for PHP pages (dynamic data), falling back to Cache
  if (requestUrl.pathname.endsWith('.php') || requestUrl.pathname === '/') {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // Clone the response and cache it
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseClone);
          });
          return response;
        })
        .catch(() => {
          // If offline, serve from cache
          return caches.match(event.request);
        })
    );
    return;
  }

  // Cache-First for static assets, models, and external CDN resources
  event.respondWith(
    caches.match(event.request).then(cachedResponse => {
      if (cachedResponse) {
        return cachedResponse;
      }

      return fetch(event.request).then(response => {
        // Cache new static resources dynamically
        if (response.status === 200) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseClone);
          });
        }
        return response;
      });
    })
  );
});
