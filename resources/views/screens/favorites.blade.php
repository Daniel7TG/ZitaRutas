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
    @include('partials.bottom-nav')
</div>
