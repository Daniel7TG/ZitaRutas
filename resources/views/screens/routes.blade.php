<div class="h-100 w-100 position-relative d-flex flex-column">
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
    <div class="flex-grow-1" style="position: relative; min-height: 400px;">
        <div id="leaflet-map"></div>
    </div>

    <!-- Panel deslizable inferior de Rutas Cercanas (Bottom Sheet) -->
    <div id="routesBottomSheet" class="routes-bottom-sheet">
        <!-- Drag Handle con 3 líneas horizontales -->
        <div class="drag-handle" id="dragHandle">
            <div class="drag-handle-lines">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <div class="routes-sheet-content">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="text-white fs-6 mb-0" style="font-family: var(--tr-font-title);"><i
                        class="fa-solid fa-map-pin text-success me-2"></i>Rutas en Zitácuaro</h3>
            </div>

            <!-- Renderizado Dinámico de Rutas desde la base de datos usando Eloquent -->
            <div class="d-flex flex-column gap-2" style="max-height: 250px; overflow-y: auto;">
                @php
                    $routesListToShow = $focusedRouteId
                        ? $rutas->where('id', $focusedRouteId)
                        : $rutas->take(12);
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
    </div>

</div>
