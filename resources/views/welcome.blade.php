@extends('layouts.app')

@section('content')
    @php
        // Detectamos la pantalla activa de forma dinámica a través de los Query Parameters de Laravel (PHP)
        $activeScreen = request()->query('screen', 'welcome');
        $activeRouteId = request()->query('route_id', '1'); // Por defecto, la primera ruta
        $activeUnit = request()->query('unit', 'AM3-A');

        // Helper en PHP para parsear los datos unificados en la columna 'color' de nuestra BD
        if (!function_exists('parseRouteData')) {
            function parseRouteData($ruta)
            {
                $colorString = $ruta->color;
                $parts = explode(' - ', $colorString);

                // Extraer color hexadecimal
                $hex = $parts[0] ?? '#10b981';

                // Reconstruir el nombre de la ruta
                $namePart = implode(' - ', array_slice($parts, 1));

                $routeCode = '';
                // Extraer el código numérico original de la ruta que está entre paréntesis (ej. 15257790)
                if (preg_match('/\((\d+)\)/', $namePart, $matches)) {
                    $routeCode = $matches[1];
                    $name = trim(str_replace("({$routeCode})", '', $namePart));
                } else {
                    $name = trim($namePart);
                    $routeCode = $ruta->id;
                }

                // Fallback si no hay nombre
                if (empty($name)) {
                    $name = 'Ruta ' . $routeCode;
                }

                // Generar una abreviación corta de 3-4 caracteres para la insignia (ej. AM3, RA2, AZ11)
                $shortName = '';
                if (preg_match('/([a-zA-Z]+[0-9]+)/i', $name, $matches)) {
                    $shortName = strtoupper($matches[1]);
                } else {
                    // Tomar las iniciales de las palabras
                    $words = explode(' ', $name);
                    if (count($words) >= 2) {
                        $shortName = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                    } else {
                        $shortName = 'R' . substr($routeCode, -2);
                    }
                }

                return [
                    'hex' => $hex,
                    'name' => $name,
                    'code' => $routeCode,
                    'short' => substr($shortName, 0, 5),
                ];
            }
        }

        // Buscamos la ruta activa seleccionada mediante Eloquent
        $selectedRuta = $rutas->firstWhere('id', $activeRouteId) ?? $rutas->first();
        $selectedRutaInfo = $selectedRuta ? parseRouteData($selectedRuta) : null;

        // Pre-computar datos para JavaScript y evitar errores de compilación de Blade con closures complejas
        $realRoutesData = [];
        $selectedRouteCoordsData = [];
        $focusedRouteId = request()->query('focused_route_id', null);

        if (in_array($activeScreen, ['routes', 'tracking', 'route-detail'])) {
            if ($focusedRouteId) {
                $focusedRuta = $rutas->firstWhere('id', $focusedRouteId);
                if ($focusedRuta) {
                    $realRoutesData = [
                        [
                            'id' => $focusedRuta->id,
                            'color' => parseRouteData($focusedRuta)['hex'],
                            'name' => parseRouteData($focusedRuta)['name'],
                            'short' => parseRouteData($focusedRuta)['short'],
                            'coordinates' => $focusedRuta->relationLoaded('puntosNavegacion')
                                ? $focusedRuta->puntosNavegacion->map(
                                    fn($p) => [(float) $p->latitud, (float) $p->longitud],
                                )
                                : collect(),
                        ],
                    ];
                }
            } else {
                $realRoutesData = $rutas
                    //->random(min($rutas->count(), 8))
                    ->take(149)
                    ->map(function ($r) {
                        $info = parseRouteData($r);
                        $coords = $r->relationLoaded('puntosNavegacion')
                            ? $r->puntosNavegacion->map(fn($p) => [(float) $p->latitud, (float) $p->longitud])
                            : collect();
                        return [
                            'id' => $r->id,
                            'color' => $info['hex'],
                            'name' => $info['name'],
                            'short' => $info['short'],
                            'coordinates' => $coords,
                        ];
                    })
                    ->toArray();
            }

            $selectedRouteCoordsData =
                $selectedRuta && $selectedRuta->relationLoaded('puntosNavegacion')
                    ? $selectedRuta->puntosNavegacion
                        ->map(fn($p) => [(float) $p->latitud, (float) $p->longitud])
                        ->toArray()
                    : [];
        }
    @endphp

    @if ($activeScreen == 'welcome')
        @include('screens.welcome')
    @endif

    @if ($activeScreen == 'permissions')
        @include('screens.permissions')
    @endif

    @if ($activeScreen == 'routes')
        @include('screens.routes')
    @endif

    @if ($activeScreen == 'route-detail')
        @include('screens.route-detail')
    @endif

    @if ($activeScreen == 'tracking')
        @include('screens.tracking')
    @endif

    @if ($activeScreen == 'favorites')
        @include('screens.favorites')
    @endif

    @if ($activeScreen == 'routes-list')
        @include('screens.routes-list')
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const activeScreen = "{{ $activeScreen }}";

            if (activeScreen === 'routes' || activeScreen === 'tracking') {

                // Coordenadas del centro de Zitácuaro, Michoacán
                const zitacuaroCoords = [19.4357, -100.3571];

                // 1. Inicializar el mapa
                const map = L.map('leaflet-map', {
                    center: zitacuaroCoords,
                    zoom: 14,
                    zoomControl: false,
                    attributionControl: true
                });

                // 2. Cargar capa de CartoDB Dark Matter
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                    subdomains: 'abcd',
                    maxZoom: 20
                }).addTo(map);

                // 3. Crear iconos
                const busIcon = L.divIcon({
                    className: 'custom-bus-icon',
                    html: '<div class="bus-pin-glow"><i class="fa-solid fa-bus"></i></div>',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                const userIcon = L.divIcon({
                    className: 'custom-user-icon',
                    html: '<div class="user-pin-glow"></div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                // 4. Lógica de renderizado dinámico usando datos reales de base de datos
                if (activeScreen === 'routes') {
                    // Serializamos las rutas a renderizar (que si está enfocada será solo una)
                    const realRoutes = @json($realRoutesData);
                    const isFocused = @json((bool) $focusedRouteId);
                    let routeBounds = L.latLngBounds();

                    // Dibujar las rutas reales en el mapa
                    realRoutes.forEach(function(route) {
                        if (route.coordinates.length > 0) {
                            const polyline = L.polyline(route.coordinates, {
                                color: route.color,
                                weight: 6,
                                opacity: 0.9
                            }).addTo(map);

                            if (isFocused) {
                                polyline.getLatLngs().forEach(latlng => routeBounds.extend(latlng));
                            }

                            // Colocar una combi simulada en el punto medio de cada ruta real
                            const midIndex = Math.floor(route.coordinates.length / 2);
                            const midPoint = route.coordinates[midIndex];

                            if (midPoint) {
                                // Cambiamos el color de la combi dinámicamente inyectando estilo inline
                                const styledBusIcon = L.divIcon({
                                    className: 'custom-bus-icon',
                                    html: `<div class="bus-pin-glow" style="background-color: ${route.color}; box-shadow: 0 0 15px ${route.color};"><i class="fa-solid fa-bus text-white"></i></div>`,
                                    iconSize: [32, 32],
                                    iconAnchor: [16, 16]
                                });

                                L.marker(midPoint, {
                                        icon: styledBusIcon
                                    })
                                    .addTo(map)
                                    .bindPopup(
                                        `<strong style="color: ${route.color}">${route.short}</strong><br><span class="text-dark">${route.name}</span>`
                                    );
                            }
                        }
                    });

                    if (!isFocused) {
                        // Colocar marcador del Usuario
                        L.marker(zitacuaroCoords, {
                                icon: userIcon
                            })
                            .addTo(map)
                            .bindPopup(
                                "<strong class='text-primary'>Tú estás aquí</strong><br><span class='text-dark'>Centro de Zitácuaro</span>"
                            )
                            .openPopup();

                        // Ajustar mapa a los límites de Zitácuaro
                        map.setView(zitacuaroCoords, 14);
                    } else if (realRoutes.length > 0 && routeBounds.isValid()) {
                        map.fitBounds(routeBounds, {
                            padding: [50, 50]
                        });
                    }

                } else if (activeScreen === 'tracking') {
                    // Serializamos las coordenadas geográficas REALES de la ruta seleccionada
                    const selectedRouteCoords = @json($selectedRouteCoordsData);
                    const selectedRouteColor = "{{ $selectedRutaInfo['hex'] }}";

                    if (selectedRouteCoords.length > 0) {
                        // Dibujamos la trayectoria real completa guardada en la base de datos
                        const polyline = L.polyline(selectedRouteCoords, {
                            color: selectedRouteColor,
                            weight: 6,
                            opacity: 0.8
                        }).addTo(map);

                        // Centrar el mapa en el trayecto
                        map.fitBounds(polyline.getBounds(), {
                            padding: [30, 30]
                        });

                        // Ubicación del usuario (en el centro del mapa)
                        const userPosition = selectedRouteCoords[Math.floor(selectedRouteCoords.length * 0.7)] ||
                            zitacuaroCoords;
                        L.marker(userPosition, {
                                icon: userIcon
                            })
                            .addTo(map)
                            .bindPopup("<strong class='text-primary'>Tú estás aquí (En paradero)</strong>")
                            .openPopup();

                        // Combi Marcador con color dinámico de la ruta
                        const styledBusIcon = L.divIcon({
                            className: 'custom-bus-icon',
                            html: `<div class="bus-pin-glow" style="background-color: ${selectedRouteColor}; box-shadow: 0 0 15px ${selectedRouteColor};"><i class="fa-solid fa-bus"></i></div>`,
                            iconSize: [32, 32],
                            iconAnchor: [16, 16]
                        });

                        // Colocamos la combi al inicio de la ruta real
                        const busMarker = L.marker(selectedRouteCoords[0], {
                            icon: styledBusIcon
                        }).addTo(map);
                        busMarker.bindPopup(
                            `<strong>Combi {{ $activeUnit }}</strong><br><span class="text-dark">Aproximándose...</span>`
                        ).openPopup();

                        // Animación Realista: Mover la combi paso a paso a lo largo de las coordenadas geográficas de la BD
                        let step = 0;
                        const maxSteps = Math.min(selectedRouteCoords.length, 35); // Limitar para demostración ágil

                        const intervalId = setInterval(function() {
                            if (step < maxSteps) {
                                const nextPos = selectedRouteCoords[step];
                                busMarker.setLatLng(nextPos);

                                // Calcular progreso porcentual del viaje
                                const progress = Math.round((step / (maxSteps - 1)) * 100);

                                // Actualizar barra de progreso y combi en el DOM
                                document.getElementById('tracking-progress-bar').style.width =
                                    `${progress}%`;
                                document.getElementById('tracking-combi-indicator').style.left =
                                    `${Math.min(progress, 90)}%`;

                                // Calcular ETA decreciente
                                const minutesLeft = Math.max(1, Math.round(((maxSteps - step) / maxSteps) *
                                    6));
                                document.getElementById('dynamic-eta').innerText = `${minutesLeft} min`;

                                if (step === maxSteps - 1) {
                                    busMarker.bindPopup(
                                        `<strong>Combi {{ $activeUnit }}</strong><br><span class="text-dark">¡Ha llegado a tu parada!</span>`
                                    ).openPopup();
                                }

                                map.panTo(nextPos);
                                step++;
                            } else {
                                step = 0; // Reiniciar simulación para demostración continua
                            }
                        }, 4000); // Mueve la combi cada 4 segundos
                    }
                }

                // ================================================================
                // 5. CONEXIÓN WEBSOCKET PARA RASTREO MULTIPLAYER EN TIEMPO REAL
                // ================================================================
                const wsHost = window.location.hostname || "127.0.0.1";
                const wsPort = "8080";
                const socket = new WebSocket(`ws://${wsHost}:${wsPort}`);
                const realTimeMarkers = {};

                socket.onopen = function() {
                    console.log("🔌 Conectado exitosamente al servidor WebSocket de ZitaRutas");
                };

                socket.onmessage = function(event) {
                    try {
                        const message = JSON.parse(event.data);
                        if (message.type === 'location_update') {
                            const {
                                client_id,
                                latitud,
                                longitud,
                                orientacion,
                                velocidad
                            } = message;

                            console.log(`📍 Ubicación en tiempo real recibida para combi #${client_id}:`,
                                latitud, longitud);

                            // Si ya tenemos un marcador para esta combi, lo actualizamos con animación
                            if (realTimeMarkers[client_id]) {
                                realTimeMarkers[client_id].setLatLng([latitud, longitud]);
                                realTimeMarkers[client_id].getPopup().setContent(
                                    `<strong>Combi en Vivo (#${client_id})</strong><br>` +
                                    `<span class="text-dark">🚗 Velocidad: ${velocidad} km/h<br>🧭 Orientación: ${orientacion}°</span>`
                                );
                            } else {
                                // Crear un marcador de combi en vivo de color verde con borde neón
                                const liveColor = "#10b981";
                                const liveBusIcon = L.divIcon({
                                    className: 'custom-bus-icon',
                                    html: `<div class="bus-pin-glow" style="background-color: ${liveColor}; box-shadow: 0 0 18px ${liveColor}; border: 3px solid #ff007f;"><i class="fa-solid fa-bus text-white"></i></div>`,
                                    iconSize: [32, 32],
                                    iconAnchor: [16, 16]
                                });

                                const marker = L.marker([latitud, longitud], {
                                    icon: liveBusIcon
                                }).addTo(map);
                                marker.bindPopup(
                                    `<strong>Combi en Vivo (#${client_id})</strong><br>` +
                                    `<span class="text-dark">🚗 Velocidad: ${velocidad} km/h<br>🧭 Orientación: ${orientacion}°</span>`
                                ).openPopup();

                                realTimeMarkers[client_id] = marker;
                            }

                            // Si estamos en la pantalla de seguimiento individual
                            if (activeScreen === 'tracking') {
                                // Actualizar la interfaz de usuario con los datos reales recibidos por WebSocket
                                const etaElement = document.getElementById('dynamic-eta');
                                if (etaElement) {
                                    etaElement.innerHTML =
                                        `<span class="text-success animate-pulse"><i class="fa-solid fa-circle me-1"></i> EN VIVO</span>`;
                                }

                                // Centrar el mapa dinámicamente en el GPS del conductor
                                map.panTo([latitud, longitud]);
                            }
                        }
                    } catch (e) {
                        console.error("Error al procesar mensaje de WebSocket:", e);
                    }
                };

                socket.onerror = function(error) {
                    console.warn(
                        "⚠️ No se pudo conectar al servidor WebSocket (¿Está ejecutándose 'php artisan websocket:serve'?)"
                    );
                };

                socket.onclose = function() {
                    console.log("🔌 Conexión WebSocket cerrada.");
                };
            }
        });
    </script>
@endpush
