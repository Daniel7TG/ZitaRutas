<div class="h-100 w-100 position-relative d-flex flex-column">
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
                    <h2 class="text-white fs-7 m-0 fw-bold">Seguimiento </h2>
                    <small class="text-muted-custom fs-8">{{ $selectedRutaInfo['short'] }} • Combi
                        {{ $activeUnit }}</small>
                </div>
            </div>

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

</div>
