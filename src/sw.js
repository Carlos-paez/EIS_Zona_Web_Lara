var CACHE_NAME = 'eis-cache-v1';

var STATIC_ASSETS = [
  'Public/css/material-icons.css',
  'Public/css/materialize.min.css',
  'Public/css/styles.css',
  'Public/css/login.css',
  'Public/js/jquery-3.7.1.min.js',
  'Public/js/materialize.min.js',
  'Public/js/app.core.js',
  'Public/js/app.init.js',
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

  // Cache First for static assets
  if (isStaticAsset(url.pathname)) {
    event.respondWith(
      caches.match(request).then(function (cached) {
        return cached || fetch(request);
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
