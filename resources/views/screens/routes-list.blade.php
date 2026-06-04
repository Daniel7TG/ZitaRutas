<div class="h-100 w-100 d-flex flex-column p-3">
    <!-- Encabezado -->
    <div class="d-flex align-items-center justify-content-between mb-3 mt-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-bus text-success fs-4"></i>
            <h2 class="text-white fs-5 m-0" style="font-family: var(--tr-font-title);">Catálogo de Rutas</h2>
        </div>
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

    <!-- Contenedor del listado de rutas (flexible y auto-scrollable) -->
    <div class="d-flex flex-column gap-2 flex-grow-1 overflow-y-auto" id="routesListContainer"
        style="padding-right: 2px;">
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
