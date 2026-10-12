const CACHE_NAME = 'appjoteca-v1';

const urlsToCache = [
  '/Appjoteca/',
  '/Appjoteca/index.php',
  '/Appjoteca/shared/css/landing.css',
  '/Appjoteca/shared/css/theme.css',
  '/Appjoteca/shared/css/components/topbar-landing.css',
  '/Appjoteca/shared/css/components/footer.css',
  '/Appjoteca/shared/css/components/navbar.css',
  '/Appjoteca/shared/js/landing.js',
  '/Appjoteca/shared/js/global.js',
  '/Appjoteca/shared/js/menu.js',
  '/Appjoteca/shared/js/components/navbar.js',
  '/Appjoteca/shared/layouts/footer.php',
  '/Appjoteca/shared/layouts/menu-movil.php',
  '/Appjoteca/pages/sobre-nosotros/about-us.php',
  '/Appjoteca/pages/sobre-nosotros/about-us.css',
  '/Appjoteca/pages/sobre-nosotros/about-us.js',
  '/Appjoteca/pages/manual/manual.php',
  '/Appjoteca/pages/manual/manual.css',
  '/Appjoteca/pages/manual/manual.js',
  '/Appjoteca/shared/images/logo-appjoteca.svg',
  '/Appjoteca/shared/images/favicon.png',
  '/Appjoteca/shared/images/icon-192.png',
  '/Appjoteca/shared/images/icon-512.png',
  '/Appjoteca/offline.html'
];

self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function(cache) {
      return cache.addAll(urlsToCache);
    })
  );
});

self.addEventListener('activate', function(event) {
  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cache) {
          if (cache !== CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    })
  );
});

self.addEventListener('fetch', function(event) {
  // Sólo peticiones GET y del mismo origen
  if (event.request.method !== 'GET' || !event.request.url.startsWith(self.location.origin)) {
    return;
  }

  

  const requestUrl = event.request.url;

  // CACHE FIRST
  if (
    requestUrl.includes('.css') ||
    requestUrl.includes('.js') ||
    requestUrl.includes('.png') ||
    requestUrl.includes('.svg') ||
    requestUrl.includes('.jpg') ||
    requestUrl.includes('.webp') ||
    requestUrl.includes('.woff')
  ) {
    event.respondWith(
      caches.match(event.request).then(function(cachedResponse) {
        return cachedResponse || fetch(event.request);
      })
    );
    return;
  }

  // NETWORK FIRST
  event.respondWith(
    fetch(event.request)
      .then(function(networkResponse) {

        // SOLO VÁLIDO EN CACHÉ
        if (networkResponse.status === 200) {
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then(function(cache) {
            cache.put(event.request, responseClone);
          });
        }
        return networkResponse;
      })
      .catch(function() {
        // Si no hay internet, devolver lo que hay en caché
        return caches.match(event.request);
      })
  );
}).catch(function() {
  return caches.match('/Appjoteca/offline.html');
});

self.addEventListener('message', function(event) {
  if (event.data && event.data.action === 'skipWaiting') {
    self.skipWaiting();
  }
});
