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
                    ->random(min($rutas->count(), 8))
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

    <!-- ==============================================
        PANTALLA 1: BIENVENIDA / LANDING (welcome)
        ============================================== -->
    @if ($activeScreen == 'welcome')
        <div class="d-flex flex-column justify-content-between h-100 p-4 text-center" style="min-height: 800px;">
            <!-- Elemento decorativo superior -->
            <div class="mt-4">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                    <i class="fa-solid fa-route me-1"></i> ZitaRutas
                </span>
            </div>

            <!-- Contenido Principal -->
            <div class="my-auto">
                <!-- Icono/Logo de Red de Rutas Brillante -->
                <div class="position-relative mx-auto mb-4 d-flex align-items-center justify-content-center"
                    style="width: 140px; height: 140px;">
                    <div class="position-absolute w-100 h-100 rounded-circle bg-success opacity-10 blur-md"
                        style="filter: blur(20px);"></div>
                    <div class="position-relative bg-dark border border-secondary border-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 110px; height: 110px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                        <i class="fa-solid fa-circle-nodes text-success fs-1 animate-pulse"
                            style="animation: pulse-pin 2.5s infinite;"></i>
                    </div>
                </div>

                <h1 class="display-6 fw-extrabold text-white mb-2" style="font-family: var(--tr-font-title);">ZitaRutas</h1>
                <p class="text-success fw-semibold mb-4"
                    style="letter-spacing: 0.1em; text-transform: uppercase; font-size: 13px;">Zitácuaro en Tiempo Real</p>

                <h2 class="fs-4 text-white mb-3" style="font-weight: 600;">Muévete mejor por tu ciudad</h2>
                <p class="text-muted-custom px-2 fs-6 mb-4" style="font-size: 14px; line-height: 1.6;">
                    Sigue las combis en vivo. Conoce en tiempo real cuándo llegará tu transporte y planifica tus
                    desplazamientos sin preocupaciones.
                </p>
            </div>

            <!-- Botón de acción y footer -->
            <div class="mb-4">
                <a href="?screen=permissions"
                    class="btn btn-neon-green w-100 py-3 mb-3 d-flex align-items-center justify-content-center gap-2">
                    Comenzar <i class="fa-solid fa-arrow-right fs-5"></i>
                </a>
                <small class="text-muted-custom fs-7">
                    <i class="fa-solid fa-shield-halved me-1"></i> Configura tu experiencia en segundos
                </small>
            </div>
        </div>
    @endif

    <!-- ==============================================
         PANTALLA 2: PERMISOS DE UBICACIÓN (permissions)
        ============================================== -->
    @if ($activeScreen == 'permissions')
        <div class="d-flex flex-column justify-content-between h-100 p-4" style="min-height: 800px;">
            <!-- Encabezado -->
            <div class="mt-2">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-leaf text-success fs-4"></i>
                    <span class="fs-5 fw-bold text-white" style="font-family: var(--tr-font-title);">ZitaRutas</span>
                </div>
                <hr class="border-secondary border-opacity-25 my-2">
            </div>

            <!-- Cuerpo de las tarjetas -->
            <div class="my-auto">
                <h2 class="text-white fs-4 mb-4" style="font-family: var(--tr-font-title);">Configuración de Ubicación</h2>

                <!-- Card 1 -->
                <div class="glass-card p-3 mb-3 d-flex align-items-start gap-3">
                    <div
                        class="bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 p-2 text-success">
                        <i class="fa-solid fa-map-location-dot fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fs-6 text-white mb-1">Tu ubicación en el mapa</h3>
                        <p class="text-muted-custom m-0 fs-7" style="line-height: 1.4;">Visualiza exactamente dónde te
                            encuentras y descubre las rutas de transporte público más cercanas a ti de forma dinámica.</p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="glass-card p-3 mb-3 d-flex align-items-start gap-3">
                    <div
                        class="bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 p-2 text-primary">
                        <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fs-6 text-white mb-1">Tiempos de llegada precisos</h3>
                        <p class="text-muted-custom m-0 fs-7" style="line-height: 1.4;">Consulta el tiempo estimado de paso
                            del próximo vehículo y observa su desplazamiento en tiempo real.</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="glass-card p-3 mb-4 d-flex align-items-start gap-3">
                    <div
                        class="bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 p-2 text-warning">
                        <i class="fa-solid fa-circle-question fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fs-6 text-white mb-1">¿Por qué lo necesitamos?</h3>
                        <p class="text-muted-custom m-0 fs-7" style="line-height: 1.4;">Se usa para centrar el mapa en tu
                            posición, detectar rutas automáticamente y afinar algoritmos predictivos.</p>
                    </div>
                </div>

                <!-- Checkbox Switch -->
                <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-location-crosshairs text-success fs-5"></i>
                        <span class="text-white fs-6 fw-medium">Ubicación precisa (GPS)</span>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input form-check-input-custom" type="checkbox" id="gpsToggle" checked>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="mb-4">
                <a href="?screen=routes"
                    class="btn btn-neon-green w-100 py-3 mb-3 d-flex align-items-center justify-content-center gap-2">
                    Permitir y continuar al mapa <i class="fa-solid fa-location-arrow"></i>
                </a>
                <div class="text-center">
                    <a href="?screen=welcome" class="text-muted-custom text-decoration-none fs-7 hover-white"><i
                            class="fa-solid fa-chevron-left me-1"></i> Volver</a>
                </div>
            </div>
        </div>
    @endif

    <!-- ==============================================
                                                             PANTALLA 3: MAPA GENERAL / RUTAS CERCANAS (routes)
                                                             ============================================== -->
    @if ($activeScreen == 'routes')
        <div class="h-100 position-relative d-flex flex-column" style="min-height: 800px; padding-bottom: 72px;">
            <!-- Barra de búsqueda flotante superior -->
            <div class="position-absolute top-0 left-0 w-100 p-3" style="z-index: 100;">
                <div class="glass-card p-2 d-flex align-items-center gap-2 shadow-lg"
                    style="background: rgba(13, 17, 26, 0.85);">
                    <div class="d-flex align-items-center gap-2 ps-2 text-success">
                        <i class="fa-solid fa-leaf fs-5 animate-pulse"></i>
                        <span class="fw-bold text-white"
                            style="font-family: var(--tr-font-title); font-size: 15px;">ZitaRutas</span>
                    </div>
                </div>

                @if ($focusedRouteId)
                    @php
                        $focusedRutaModel = $rutas->firstWhere('id', $focusedRouteId);
                        $focusedInfo = $focusedRutaModel ? parseRouteData($focusedRutaModel) : null;
                    @endphp
                    @if ($focusedInfo)
                        <div class="glass-card p-2 mt-2 d-flex align-items-center justify-content-between shadow-lg"
                            style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 14px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="route-badge"
                                    style="background-color: {{ $focusedInfo['hex'] }}; width: 28px; height: 28px; font-size: 10px; border-radius: 6px;">
                                    {{ $focusedInfo['short'] }}</div>
                                <span class="text-white fs-8 fw-semibold">{{ $focusedInfo['name'] }}</span>
                            </div>
                            <a href="?screen=routes"
                                class="btn btn-sm btn-dark-secondary py-1 px-2 fs-9 text-danger border-0 d-flex align-items-center gap-1"
                                style="background-color: rgba(239, 68, 68, 0.1); border-radius: 8px;">
                                <i class="fa-solid fa-xmark"></i> Quitar Filtro
                            </a>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Contenedor del Mapa Leaflet -->
            <div class="flex-grow-1" style="height: 480px; min-height: 400px; position: relative;">
                <div id="leaflet-map"></div>
            </div>

            <!-- Panel deslizable inferior de Rutas Cercanas -->
            <div class="glass-card rounded-bottom-0 border-bottom-0 p-3"
                style="background: rgba(13, 17, 26, 0.95); z-index: 5;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="text-white fs-6 mb-0" style="font-family: var(--tr-font-title);"><i
                            class="fa-solid fa-map-pin text-success me-2"></i>Rutas en Zitácuaro</h3>
                </div>

                <!-- Renderizado DInámico de Rutas desde la base de datos usando Eloquent -->
                <div class="d-flex flex-column gap-2" style="max-height: 250px; overflow-y: auto;">
                    @php
                        $routesListToShow = $focusedRouteId ? $rutas->where('id', $focusedRouteId) : $rutas->take(12);
                    @endphp
                    @foreach ($routesListToShow as $ruta)
                        @php
                            $info = parseRouteData($ruta);
                        @endphp
                        <div
                            class="glass-card p-2 border-opacity-50 hover-glow d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="route-badge" style="background-color: {{ $info['hex'] }};">
                                    {{ $info['short'] }}</div>
                                <div>
                                    <h4 class="fs-7 text-white mb-0 fw-semibold">{{ $info['name'] }}</h4>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="text-success fw-bold fs-7 d-block">{{ rand(2, 15) }} min</span>
                                <a href="?screen=route-detail&route_id={{ $ruta->id }}"
                                    class="text-success text-decoration-none fs-8 fw-semibold">Ver seguimiento <i
                                        class="fa-solid fa-chevron-right fs-9"></i></a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Barra de Navegación Inferior Móvil -->
            <div class="mobile-bottom-nav">
                <a href="?screen=routes" class="nav-item-custom active">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Mapa</span>
                </a>
                <a href="?screen=routes-list" class="nav-item-custom">
                    <i class="fa-solid fa-bus"></i>
                    <span>Rutas</span>
                </a>
                <a href="?screen=favorites" class="nav-item-custom">
                    <i class="fa-solid fa-star"></i>
                    <span>Favoritos</span>
                </a>
            </div>
        </div>
    @endif

    <!-- ==============================================
        PANTALLA 4: DETALLE DE RUTA ACTIVA (route-detail)
        ============================================== -->
    @if ($activeScreen == 'route-detail')
        <div class="h-100 d-flex flex-column justify-content-between p-3"
            style="min-height: 800px; padding-bottom: 72px;">
            <div>
                <!-- Encabezado de Navegación -->
                <div class="d-flex align-items-center justify-content-between mb-3 mt-2">
                    <a href="?screen=routes"
                        class="btn btn-dark-secondary p-2 rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-chevron-left text-success"></i>
                    </a>
                    <h2 class="text-white fs-6 m-0" style="font-family: var(--tr-font-title);">Detalle:
                        {{ $selectedRutaInfo['short'] }}</h2>
                    <button
                        class="btn btn-dark-secondary p-2 rounded-circle d-flex align-items-center justify-content-center text-warning"
                        style="width: 38px; height: 38px; border-color: rgba(245, 158, 11, 0.25);">
                        <i class="fa-solid fa-star"></i>
                    </button>
                </div>

                <!-- Tarjeta de Estadísticas de Ruta -->
                <div class="glass-card p-3 mb-3">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="route-badge"
                            style="background-color: {{ $selectedRutaInfo['hex'] }}; width: 44px; height: 44px; font-size: 16px;">
                            {{ $selectedRutaInfo['short'] }}</div>
                        <div>
                            <h3 class="fs-6 text-white m-0 fw-bold">{{ $selectedRutaInfo['name'] }}</h3>
                        </div>
                    </div>
                    <hr class="border-secondary border-opacity-25 my-2">
                    <div class="row text-center">
                        <div class="col-4 border-end border-secondary border-opacity-25">
                            <small class="text-muted-custom d-block fs-8 mb-1">Frecuencia</small>
                            <span class="text-success fw-bold fs-6">3 - 5 min</span>
                        </div>
                        <div class="col-4 border-end border-secondary border-opacity-25">
                            <small class="text-muted-custom d-block fs-8 mb-1">Unidades</small>
                            <span class="text-white fw-bold fs-6"><i class="fa-solid fa-bus text-success me-1"></i>
                                3</span>
                        </div>
                        <div class="col-4">
                            <small class="text-muted-custom d-block fs-8 mb-1">Coordenadas</small>
                            <span class="text-white fw-bold fs-6">{{ $selectedRuta->puntosNavegacion->count() }}
                                pts</span>
                        </div>
                    </div>
                </div>

                <!-- Estimaciones por Paradas clave -->
                <div class="glass-card p-3 mb-4">
                    <h3 class="text-white fs-7 mb-3" style="font-family: var(--tr-font-title);"><i
                            class="fa-regular fa-clock text-success me-2"></i>Tiempos estimados de llegada</h3>
                    <div class="d-flex flex-column gap-2">
                        <div
                            class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary border-opacity-10">
                            <span class="text-muted-custom fs-8">Punto de Origen</span>
                            <span class="text-success fw-semibold fs-7">2 min</span>
                        </div>
                        <div
                            class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary border-opacity-10">
                            <span class="text-muted-custom fs-8">Centro de Zitácuaro</span>
                            <span class="text-success fw-semibold fs-7">6 min</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span class="text-muted-custom fs-8">Punto de Destino</span>
                            <span class="text-success fw-semibold fs-7">14 min</span>
                        </div>
                    </div>
                </div>

                <!-- Unidades Activas en Ruta -->
                <div>
                    <h3 class="text-white fs-7 mb-3" style="font-family: var(--tr-font-title);"><i
                            class="fa-solid fa-signal text-success me-2"></i>Unidades en tránsito activo</h3>

                    <div class="d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
                        <!-- Unidad 1 -->
                        <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="position-relative">
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle"></span>
                                    <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 42px; height: 42px;">
                                        <i class="fa-solid fa-bus text-success fs-5"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-white fs-7 m-0 fw-semibold">Combi {{ $selectedRutaInfo['short'] }}-A
                                    </h4>
                                    <p class="text-muted-custom m-0 fs-8">Hacia Terminal • Zitácuaro Centro</p>
                                    <span class="text-success fs-8 fw-medium">Llega en 4 min</span>
                                </div>
                            </div>
                            <a href="?screen=tracking&route_id={{ $selectedRuta->id }}&unit={{ $selectedRutaInfo['short'] }}-A"
                                class="btn btn-neon-green py-2 px-3 fs-8">Seguir</a>
                        </div>

                        <!-- Unidad 2 -->
                        <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="position-relative">
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle"></span>
                                    <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 42px; height: 42px;">
                                        <i class="fa-solid fa-bus text-success fs-5"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-white fs-7 m-0 fw-semibold">Combi {{ $selectedRutaInfo['short'] }}-B
                                    </h4>
                                    <p class="text-muted-custom m-0 fs-8">Hacia Terminal • Tramo Intermedio</p>
                                    <span class="text-success fs-8 fw-medium">Llega en 9 min</span>
                                </div>
                            </div>
                            <a href="?screen=tracking&route_id={{ $selectedRuta->id }}&unit={{ $selectedRutaInfo['short'] }}-B"
                                class="btn btn-neon-green py-2 px-3 fs-8">Seguir</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de Navegación Inferior -->
            <div class="mobile-bottom-nav">
                <a href="?screen=routes" class="nav-item-custom active">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Mapa</span>
                </a>
                <a href="?screen=routes-list" class="nav-item-custom">
                    <i class="fa-solid fa-bus"></i>
                    <span>Rutas</span>
                </a>
                <a href="?screen=favorites" class="nav-item-custom">
                    <i class="fa-solid fa-star"></i>
                    <span>Favoritos</span>
                </a>
            </div>
        </div>
    @endif

    <!-- ==============================================
                                                             PANTALLA 5: SEGUIMIENTO EN VIVO (tracking)
                                                             ============================================== -->
    @if ($activeScreen == 'tracking')
        <div class="h-100 position-relative d-flex flex-column" style="min-height: 800px; padding-bottom: 72px;">
            <!-- Botón Flotante Atrás y Titulo -->
            <div class="position-absolute top-0 left-0 w-100 p-3" style="z-index: 100;">
                <div class="glass-card p-2 d-flex align-items-center justify-content-between shadow-lg"
                    style="background: rgba(13, 17, 26, 0.85);">
                    <div class="d-flex align-items-center gap-3">
                        <a href="?screen=route-detail&route_id={{ $selectedRuta->id }}"
                            class="btn btn-dark-secondary p-2 rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 38px; height: 38px; border: none;">
                            <i class="fa-solid fa-arrow-left text-success fs-5"></i>
                        </a>
                        <div>
                            <h2 class="text-white fs-7 m-0 fw-bold">Seguimiento en Vivo</h2>
                            <small class="text-muted-custom fs-8">{{ $selectedRutaInfo['short'] }} • Combi
                                {{ $activeUnit }}</small>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle fs-8 px-2 py-1"><i
                            class="fa-solid fa-circle animate-pulse me-1"></i>En vivo</span>
                </div>
            </div>

            <!-- Contenedor del Mapa Leaflet -->
            <div class="flex-grow-1" style="height: 380px; min-height: 300px; position: relative;">
                <div id="leaflet-map"></div>
            </div>

            <!-- Glass Card de Detalles del Estado de Tránsito -->
            <div class="glass-card rounded-bottom-0 border-bottom-0 p-3"
                style="background: rgba(13, 17, 26, 0.95); z-index: 5;">

                <!-- Grid de ETA y Velocidad -->
                <div class="row text-center mb-3">
                    <div class="col-6 border-end border-secondary border-opacity-25">
                        <small class="text-muted-custom d-block fs-8 mb-1">LLEGADA ESTIMADA</small>
                        <span class="text-success fw-extrabold fs-4 animate-pulse"><i
                                class="fa-solid fa-circle-check me-1 fs-5"></i> <span id="dynamic-eta">4 min</span></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted-custom d-block fs-8 mb-1">VELOCIDAD</small>
                        <span class="text-white fw-bold fs-4"><i class="fa-solid fa-gauge text-success me-1 fs-5"></i> 28
                            km/h</span>
                    </div>
                </div>

                <!-- Barra de Progreso Visual de Ruta -->
                <div class="px-2 mb-4">
                    <div class="d-flex justify-content-between align-items-center fs-8 text-muted-custom mb-2">
                        <span>Punto de Origen</span>
                        <span class="text-success fw-medium">Trayecto Activo</span>
                        <span>Destino Final</span>
                    </div>
                    <div class="position-relative py-2">
                        <div class="progress bg-secondary bg-opacity-25" style="height: 6px; border-radius: 3px;">
                            <div class="progress-bar bg-success" id="tracking-progress-bar" role="progressbar"
                                style="width: 30%;" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <!-- Combi Icon Indicator -->
                        <div class="position-absolute text-success" id="tracking-combi-indicator"
                            style="left: 30%; top: -3px; font-size: 15px; text-shadow: 0 0 10px rgba(16, 185, 129, 0.8); transition: left 3s ease;">
                            <i class="fa-solid fa-bus text-success"></i>
                        </div>
                    </div>
                </div>

                <!-- Timeline de Paradas -->
                <div class="mb-3">
                    <h3 class="text-white fs-7 mb-3" style="font-family: var(--tr-font-title);"><i
                            class="fa-solid fa-route text-success me-2"></i>Puntos del recorrido</h3>

                    <div class="d-flex flex-column gap-2" style="max-height: 160px; overflow-y: auto;">
                        <div class="d-flex align-items-center justify-content-between py-2 px-2 rounded"
                            style="background-color: rgba(16, 185, 129, 0.08); border-left: 3px solid var(--tr-green-primary);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-dot text-success fs-7 animate-pulse"></i>
                                <span class="text-white fs-7 fw-semibold">Zitácuaro (Ubicación en Ruta)</span>
                            </div>
                            <span class="badge bg-success bg-opacity-20 text-success fs-8">Procesando GPS</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between py-1 px-2 rounded">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-regular fa-circle text-muted-custom fs-8"></i>
                                <span class="text-muted-custom fs-8">Puntos en base de datos:
                                    {{ $selectedRuta->puntosNavegacion->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="row g-2">
                    <div class="col-6">
                        <a href="?screen=route-detail&route_id={{ $selectedRuta->id }}"
                            class="btn btn-dark-secondary w-100 py-3 fs-7">Cambiar unidad</a>
                    </div>
                    <div class="col-6">
                        <a href="?screen=routes" class="btn btn-neon-green w-100 py-3 fs-7">Ver todas las rutas</a>
                    </div>
                </div>
            </div>

            <!-- Barra de Navegación Inferior -->
            <div class="mobile-bottom-nav">
                <a href="?screen=routes" class="nav-item-custom active">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Mapa</span>
                </a>
                <a href="?screen=routes-list" class="nav-item-custom">
                    <i class="fa-solid fa-bus"></i>
                    <span>Rutas</span>
                </a>
                <a href="?screen=favorites" class="nav-item-custom">
                    <i class="fa-solid fa-star"></i>
                    <span>Favoritos</span>
                </a>
            </div>
        </div>
    @endif

    <!-- ==============================================
                                                             PANTALLA 6: FAVORITOS Y ALERTAS (favorites)
                                                             ============================================== -->
    @if ($activeScreen == 'favorites')
        <div class="h-100 d-flex flex-column justify-content-between p-3"
            style="min-height: 800px; padding-bottom: 72px;">
            <div>
                <!-- Encabezado -->
                <div class="d-flex align-items-center gap-2 mb-3 mt-2">
                    <i class="fa-solid fa-star text-warning fs-4"></i>
                    <h2 class="text-white fs-5 m-0" style="font-family: var(--tr-font-title);">Mis Favoritos y Alertas
                    </h2>
                </div>
                <hr class="border-secondary border-opacity-25 my-2">

                <!-- Sección 1: Rutas Favoritas dinámicas -->
                <div class="mb-4">
                    <h3 class="text-white fs-7 mb-3" style="font-family: var(--tr-font-title);"><i
                            class="fa-regular fa-star text-warning me-2"></i>Rutas Favoritas</h3>

                    <div class="d-flex flex-column gap-2">
                        @foreach ($rutas->slice(5, 3) as $favRuta)
                            @php
                                $favInfo = parseRouteData($favRuta);
                            @endphp
                            <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="route-badge" style="background-color: {{ $favInfo['hex'] }};">
                                        {{ $favInfo['short'] }}</div>
                                    <div>
                                        <h4 class="text-white fs-7 m-0 fw-semibold">{{ $favInfo['name'] }}</h4>
                                        <small class="badge-active d-inline-block mt-1">Activo</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="text-success fs-8 fw-medium d-block">Próx: {{ rand(3, 12) }} min</span>
                                    <a href="?screen=tracking&route_id={{ $favRuta->id }}"
                                        class="text-success fs-8 text-decoration-none fw-semibold">Seguir</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Sección 2: Ubicaciones Frecuentes -->
                <div class="mb-4">
                    <h3 class="text-white fs-7 mb-3" style="font-family: var(--tr-font-title);"><i
                            class="fa-solid fa-map-location-dot text-success me-2"></i>Ubicaciones Frecuentes</h3>

                    <div class="row g-2">
                        <div class="col-4">
                            <div
                                class="glass-card p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-success bg-opacity-10 rounded-circle text-success d-flex align-items-center justify-content-center mb-2"
                                    style="width: 40px; height: 40px;">
                                    <i class="fa-solid fa-house fs-5"></i>
                                </div>
                                <h4 class="fs-8 text-white mb-0 fw-semibold">Casa</h4>
                                <small class="text-muted-custom fs-9 mt-1">Av. Revolución</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div
                                class="glass-card p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle text-primary d-flex align-items-center justify-content-center mb-2"
                                    style="width: 40px; height: 40px;">
                                    <i class="fa-solid fa-briefcase fs-5"></i>
                                </div>
                                <h4 class="fs-8 text-white mb-0 fw-semibold">Trabajo</h4>
                                <small class="text-muted-custom fs-9 mt-1">Col. Centro</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div
                                class="glass-card p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle text-warning d-flex align-items-center justify-content-center mb-2"
                                    style="width: 40px; height: 40px;">
                                    <i class="fa-solid fa-graduation-cap fs-5"></i>
                                </div>
                                <h4 class="fs-8 text-white mb-0 fw-semibold">UMSNH</h4>
                                <small class="text-muted-custom fs-9 mt-1">Zitácuaro</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Configuración de Alertas -->
                <div class="mb-4">
                    <h3 class="text-white fs-7 mb-3" style="font-family: var(--tr-font-title);"><i
                            class="fa-solid fa-gears text-success me-2"></i>Configuración de Alertas</h3>

                    <div class="glass-card p-3 d-flex flex-column gap-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="fs-7 text-white mb-0 fw-semibold">Ruta cerca de mí</h4>
                                <small class="text-muted-custom fs-8">Aviso cuando una ruta pase cerca de mi
                                    posición</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input form-check-input-custom" type="checkbox" id="alert1"
                                    checked>
                            </div>
                        </div>

                        <div
                            class="d-flex align-items-center justify-content-between border-top border-secondary border-opacity-10 pt-3">
                            <div>
                                <h4 class="fs-7 text-white mb-0 fw-semibold">Unidad acercándose</h4>
                                <small class="text-muted-custom fs-8">Aviso cuando el transporte esté a 1 parada</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input form-check-input-custom" type="checkbox" id="alert2"
                                    checked>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Barra de Navegación Inferior -->
            <div class="mobile-bottom-nav">
                <a href="?screen=routes" class="nav-item-custom">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Mapa</span>
                </a>
                <a href="?screen=routes-list" class="nav-item-custom">
                    <i class="fa-solid fa-bus"></i>
                    <span>Rutas</span>
                </a>
                <a href="?screen=favorites" class="nav-item-custom active">
                    <i class="fa-solid fa-star"></i>
                    <span>Favoritos</span>
                </a>
            </div>
        </div>
    @endif

    <!-- ==============================================
                                                             PANTALLA: CATALOGO DE TODAS LAS RUTAS (routes-list)
                                                             ============================================== -->
    @if ($activeScreen == 'routes-list')
        <div class="h-100 d-flex flex-column justify-content-between p-3"
            style="min-height: 800px; padding-bottom: 72px;">
            <div>
                <!-- Encabezado -->
                <div class="d-flex align-items-center justify-content-between mb-3 mt-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-bus text-success fs-4"></i>
                        <h2 class="text-white fs-5 m-0" style="font-family: var(--tr-font-title);">Catálogo de Rutas</h2>
                    </div>
                    <span
                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fs-8 px-2 py-1">{{ $rutas->count() }}
                        activas</span>
                </div>
                <hr class="border-secondary border-opacity-25 my-2">

                <!-- Buscador de rutas interactivo en tiempo real -->
                <div class="mb-3">
                    <div class="glass-card p-2 d-flex align-items-center gap-2 shadow-sm"
                        style="background: rgba(13, 17, 26, 0.65);">
                        <span class="text-muted ps-2"><i class="fa-solid fa-magnifying-glass fs-7"></i></span>
                        <input type="text" id="routesListSearch"
                            class="form-control bg-transparent border-0 text-white ps-0 fs-7"
                            placeholder="Buscar por nombre, código..." style="box-shadow: none;"
                            onkeyup="filterRoutesList()">
                    </div>
                </div>

                <!-- Contenedor del listado de rutas -->
                <div class="d-flex flex-column gap-2" id="routesListContainer"
                    style="max-height: 520px; overflow-y: auto; padding-right: 2px;">
                    @foreach ($rutas as $ruta)
                        @php
                            $info = parseRouteData($ruta);
                        @endphp
                        <div class="glass-card p-3 border-opacity-50 hover-glow d-flex align-items-center justify-content-between route-item-card"
                            data-search="{{ strtolower($info['name']) }} {{ strtolower($info['short']) }} {{ strtolower($info['code']) }}">
                            <div class="d-flex align-items-center gap-3">
                                <div class="route-badge"
                                    style="background-color: {{ $info['hex'] }}; width: 40px; height: 40px; font-size: 13px;">
                                    {{ $info['short'] }}</div>
                                <div>
                                    <h4 class="fs-7 text-white mb-1 fw-bold">{{ $info['name'] }}</h4>

                                </div>
                            </div>
                            <div class="d-flex flex-column gap-1 text-end">
                                <a href="?screen=routes&focused_route_id={{ $ruta->id }}"
                                    class="btn btn-neon-green py-1 px-3 fs-8 fw-semibold" style="border-radius: 8px;">
                                    <i class="fa-solid fa-crosshairs me-1"></i> Enfocar
                                </a>
                                <a href="?screen=route-detail&route_id={{ $ruta->id }}"
                                    class="text-success text-decoration-none fs-8 fw-medium mt-1">Ver Detalles <i
                                        class="fa-solid fa-chevron-right fs-9"></i></a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Barra de Navegación Inferior -->
            <div class="mobile-bottom-nav">
                <a href="?screen=routes" class="nav-item-custom">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Mapa</span>
                </a>
                <a href="?screen=routes-list" class="nav-item-custom active">
                    <i class="fa-solid fa-bus"></i>
                    <span>Rutas</span>
                </a>
                <a href="?screen=favorites" class="nav-item-custom">
                    <i class="fa-solid fa-star"></i>
                    <span>Favoritos</span>
                </a>
            </div>
        </div>

        <script>
            function filterRoutesList() {
                const query = document.getElementById('routesListSearch').value.toLowerCase().trim();
                const cards = document.getElementsByClassName('route-item-card');
                for (let i = 0; i < cards.length; i++) {
                    const card = cards[i];
                    const searchData = card.getAttribute('data-search');
                    if (searchData.includes(query)) {
                        card.style.setProperty('display', 'flex', 'important');
                    } else {
                        card.style.setProperty('display', 'none', 'important');
                    }
                }
            }
        </script>
    @endif
@endsection

<!-- ==============================================
     SCRIPTS DE MAPEO INTERACTIVO (LEAFLET.JS)
     ============================================== -->
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
