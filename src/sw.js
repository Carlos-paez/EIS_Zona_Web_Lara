var CACHE_NAME = 'eis-cache-v2';

var STATIC_ASSETS = [
  'Public/css/material-icons.css',
  'Public/css/materialize.min.css',
  'Public/css/styles.css',
  'Public/css/login.css',
  'Public/js/jquery-3.7.1.min.js',
  'Public/js/materialize.min.js',
  'Public/js/app.core.js',
  'Public/js/app.init.js',
  'Public/js/app.selects.js',
  'Public/js/app.tables.js',
  'Public/js/app.ui.js',
  'Public/js/app.pos.js',
  'Public/js/app.cyber.js',
  'Public/js/app.legal.js',
  'Public/fonts/MaterialIcons-Regular.ttf',
  'manifest.json',
  'Public/icons/icon-192.svg',
  'Public/icons/icon-512.svg',
  'offline.php'
];

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function (cache) {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys.map(function (key) {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', function (event) {
  var request = event.request;
  var url = new URL(request.url);

  // Skip non-GET and browser extension requests
  if (request.method !== 'GET') return;
  if (url.origin !== self.location.origin) return;

  // Stale-While-Revalidate for static assets (JS/CSS/fonts).
  // Sirve rapido desde cache pero SIEMPRE actualiza desde la red,
  // evitando que una version vieja de los scripts quede servida
  // indefinidamente y rompa la carga de modulos como el POS.
  if (isStaticAsset(url.pathname)) {
    event.respondWith(
      caches.open(CACHE_NAME).then(function (cache) {
        return cache.match(request).then(function (cached) {
          var networkFetch = fetch(request).then(function (response) {
            if (response && response.ok) {
              cache.put(request, response.clone());
            }
            return response;
          }).catch(function () {
            return cached;
          });
          return cached || networkFetch;
        });
      })
    );
    return;
  }

  // Network First for navigation (PHP pages)
  if (request.mode === 'navigate' || isPhpPage(url)) {
    event.respondWith(
      fetch(request).then(function (response) {
        return response;
      }).catch(function () {
        return caches.match('offline.php');
      })
    );
    return;
  }
});

function isStaticAsset(pathname) {
  var staticPatterns = [
    '/Public/css/', '/Public/js/', '/Public/fonts/', '/Public/icons/', '/manifest.json'
  ];
  return staticPatterns.some(function (pattern) {
    return pathname.indexOf(pattern) !== -1;
  });
}

function isPhpPage(url) {
  return url.searchParams.has('pagina') || url.pathname === '/' || url.pathname === '/index.php';
}
