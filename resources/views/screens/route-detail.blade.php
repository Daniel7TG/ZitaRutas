<div class="h-100 w-100 d-flex flex-column p-3">
    <div class="flex-grow-1 overflow-y-auto" style="padding-right: 2px;">
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
</div>
