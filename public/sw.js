// ============================================================
// ZitaRutas - Service Worker 
// Estrategia: Network First (navegación), Cache First (assets)
// ============================================================

const CACHE_NAME = 'zitarutas-v1';
const OFFLINE_URL = '/offline.html';

// Assets estáticos para pre-cachear durante la instalación
const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/manifest.json',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    // CDN de Bootstrap
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
    // Íconos
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css',
    // Google Fonts
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap',
];

// Dominios que cacheamos con estrategia Cache First
const CACHE_FIRST_DOMAINS = [
    'cdn.jsdelivr.net',
    'cdnjs.cloudflare.com',
    'fonts.googleapis.com',
    'fonts.gstatic.com',
    'unpkg.com',
];

// Dominios de tiles de mapa (cache con límite)
const MAP_TILE_DOMAINS = [
    'basemaps.cartocdn.com',
];

// ─── INSTALL ───────────────────────────────────────────────
self.addEventListener('install', (event) => {
    console.log('🔧 [SW] Instalando Service Worker v1...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('📦 [SW] Pre-cacheando shell de la app...');
                // Usamos addAll con manejo de errores individual para que
                // un fallo en un CDN no bloquee toda la instalación
                return Promise.allSettled(
                    PRECACHE_ASSETS.map((url) =>
                        cache.add(url).catch((err) => {
                            console.warn(`⚠️ [SW] No se pudo cachear: ${url}`, err);
                        })
                    )
                );
            })
            .then(() => {
                // Activar inmediatamente sin esperar a que cierren las tabs
                return self.skipWaiting();
            })
    );
});

// ─── ACTIVATE ──────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    console.log('✅ [SW] Service Worker activado.');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => {
                        console.log(`🗑️ [SW] Eliminando cache antiguo: ${name}`);
                        return caches.delete(name);
                    })
            );
        }).then(() => {
            // Tomar control de todas las páginas abiertas inmediatamente
            return self.clients.claim();
        })
    );
});

// ─── FETCH ─────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // 1. Ignorar peticiones WebSocket y non-GET
    if (request.method !== 'GET' || url.protocol === 'ws:' || url.protocol === 'wss:') {
        return;
    }

    // 2. Navegación principal → Network First con fallback offline
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cachear la respuesta de navegación para uso offline
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    // Sin red → intentar desde cache, sino mostrar offline.html
                    return caches.match(request).then((cached) => {
                        return cached || caches.match(OFFLINE_URL);
                    });
                })
        );
        return;
    }

    // 3. CDN assets → Cache First (son versionados/inmutables)
    if (CACHE_FIRST_DOMAINS.some((domain) => url.hostname.includes(domain))) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    return cached;
                }
                return fetch(request).then((response) => {
                    if (response && response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }

    // 4. Tiles de mapa → Cache First con almacenamiento dinámico
    if (MAP_TILE_DOMAINS.some((domain) => url.hostname.includes(domain))) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    return cached;
                }
                return fetch(request).then((response) => {
                    if (response && response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                }).catch(() => {
                    // Si no hay red ni cache, devolver respuesta vacía transparente
                    return new Response('', { status: 408, statusText: 'Offline' });
                });
            })
        );
        return;
    }

    // 5. Todo lo demás → Network First
    event.respondWith(
        fetch(request)
            .then((response) => {
                // Cachear assets locales (CSS, JS, imágenes)
                if (response && response.status === 200 && url.origin === self.location.origin) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                return caches.match(request);
            })
    );
});
