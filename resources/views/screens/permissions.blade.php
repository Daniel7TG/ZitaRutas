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
