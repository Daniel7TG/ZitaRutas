<div class="h-100 w-100 position-relative d-flex flex-column">
    <!-- Barra superior con búsqueda -->
    <div class="position-absolute top-0 left-0 w-100 p-3" style="z-index: 100;">
        <div class="glass-card p-2 d-flex align-items-center gap-2 shadow-lg"
            style="background: rgba(13, 17, 26, 0.85);">
            <a href="?screen=routes"
                class="btn btn-dark-secondary p-2 rounded-circle d-flex align-items-center justify-content-center"
                style="width: 38px; height: 38px; border: none;">
                <i class="fa-solid fa-arrow-left text-success fs-5"></i>
            </a>
            <div class="flex-grow-1">
                <input type="text"
                       class="form-control bg-dark border-secondary text-white"
                       placeholder="¿A dónde quieres ir?"
                       id="destination-search"
                       readonly>
            </div>
            <button class="btn btn-neon-green py-2 px-3 d-flex align-items-center gap-1" id="btn-calculate-route">
                <i class="fa-solid fa-route"></i>
                <span class="d-none d-sm-inline">Ir</span>
            </button>
        </div>
    </div>

    <!-- Contenedor del Mapa Leaflet -->
    <div class="flex-grow-1" style="position: relative; min-height: 300px;">
        <div id="leaflet-map"></div>

        <!-- Instrucción flotante cuando no hay destino -->
        <div id="map-instruction" class="position-absolute bottom-0 start-50 translate-middle-x mb-3" style="z-index: 50;">
            <div class="glass-card px-3 py-2 text-center" style="background: rgba(13, 17, 26, 0.9);">
                <small class="text-muted-custom fs-8">
                    <i class="fa-solid fa-hand-pointer text-success me-1"></i>
                    Toca el mapa para seleccionar tu destino
                </small>
            </div>
        </div>
    </div>

    <!-- Panel inferior de itinerario -->
    <div id="itinerary-panel" class="glass-card rounded-top-4 border-top-0 p-3"
         style="background: rgba(13, 17, 26, 0.95); z-index: 5; max-height: 45vh; overflow-y: auto;">

        <!-- Resumen Origen/Destino -->
        <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-success rounded-circle" style="width: 12px; height: 12px;"></div>
                <span class="text-white fs-8">Tu ubicación</span>
            </div>
            <i class="fa-solid fa-arrow-right text-muted-custom fs-8"></i>
            <div class="d-flex align-items-center gap-2">
                <div class="bg-danger rounded-circle" style="width: 12px; height: 12px;"></div>
                <span class="text-white fs-8" id="destination-label">Selecciona destino</span>
            </div>
        </div>

        <!-- Contenedor de itinerario (se llena dinámicamente) -->
        <div id="itinerary-container">
            <div class="text-center text-muted-custom py-4">
                <i class="fa-solid fa-map-location-dot fs-1 mb-2 d-block text-success opacity-50"></i>
                <p class="fs-8 mb-0">Toca el mapa para seleccionar tu destino</p>
            </div>
        </div>
    </div>


</div>
