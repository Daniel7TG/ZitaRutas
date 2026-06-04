<div class="mobile-bottom-nav">
    <a href="?screen=routes" class="nav-item-custom {{ in_array($activeScreen, ['routes', 'route-detail', 'tracking']) ? 'active' : '' }}">
        <i class="fa-solid fa-map-location-dot"></i>
        <span>Mapa</span>
    </a>
    <a href="?screen=routes-list" class="nav-item-custom {{ $activeScreen === 'routes-list' ? 'active' : '' }}">
        <i class="fa-solid fa-bus"></i>
        <span>Rutas</span>
    </a>
    <a href="?screen=route-planner" class="nav-item-custom {{ $activeScreen === 'route-planner' ? 'active' : '' }}">
        <i class="fa-solid fa-route"></i>
        <span>Ir</span>
    </a>
    <a href="?screen=favorites" class="nav-item-custom {{ $activeScreen === 'favorites' ? 'active' : '' }}">
        <i class="fa-solid fa-star"></i>
        <span>Favoritos</span>
    </a>
</div>
