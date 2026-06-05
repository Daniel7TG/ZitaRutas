<script>
    document.addEventListener("DOMContentLoaded", function() {
        const activeScreen = "{{ $activeScreen }}";

        if (activeScreen === 'route-planner') {
            const zitacuaroCoords = [19.4357, -100.3571];

            const map = L.map('leaflet-map', {
                center: zitacuaroCoords,
                zoom: 14,
                zoomControl: false,
                attributionControl: true
            });

            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const tileAttribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>';
            const tileOptions = {
                attribution: tileAttribution,
                subdomains: 'abcd',
                maxZoom: 20
            };

            const darkTile = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', tileOptions);
            const lightTile = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', tileOptions);

            let activeTile = currentTheme === 'light' ? lightTile : darkTile;
            activeTile.addTo(map);

            document.addEventListener('themeChanged', function(e) {
                const newTheme = e.detail.theme;
                map.removeLayer(activeTile);
                activeTile = newTheme === 'light' ? lightTile : darkTile;
                activeTile.addTo(map);
            });

            const RoutePlanner = {
                state: {
                    origin: null,
                    destination: null,
                    routeData: null,
                    isLoading: false,
                    error: null,
                },

                layers: {
                    originMarker: null,
                    destinationMarker: null,
                    routePolylines: [],
                    transferMarkers: [],
                },

                config: {
                    maxTrasbordos: 3,
                    maxCaminataM: 500,
                    walkingSpeedMs: 1.4,
                },

                init() {
                    this.bindMapClick();
                    this.setOriginFromGeolocation();
                    this.bindCalculateButton();
                },

                bindMapClick() {
                    map.on('click', (e) => {
                        this.setDestination(e.latlng.lat, e.latlng.lng);
                    });
                },

                bindCalculateButton() {
                    const btn = document.getElementById('btn-calculate-route');
                    if (btn) {
                        btn.addEventListener('click', () => this.calculateRoute());
                    }
                },

                setOriginFromGeolocation() {
                    if (!navigator.geolocation) {
                        this.setOriginFallback();
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;

                            if (this.isWithinZitacuaro(lat, lng)) {
                                this.setOrigin(lat, lng);
                            } else {
                                this.setOriginFallback();
                            }
                        },
                        (error) => {
                            console.warn('Error de geolocalización:', error.message);
                            this.setOriginFallback();
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 60000
                        }
                    );
                },

                setOriginFallback() {
                    this.setOrigin(19.4357, -100.3571);
                },

                isWithinZitacuaro(lat, lng) {
                    return lat >= 19.35 && lat <= 19.52 &&
                           lng >= -100.45 && lng <= -100.25;
                },

                setOrigin(lat, lng) {
                    this.state.origin = { lat, lng };
                    this.renderOriginMarker();
                    this.updateUI();
                },

                setDestination(lat, lng) {
                    this.state.destination = { lat, lng };
                    this.renderDestinationMarker();
                    this.updateUI();

                    const instr = document.getElementById('map-instruction');
                    if (instr) instr.style.display = 'none';

                    if (this.state.origin) {
                        this.calculateRoute();
                    }
                },

                async calculateRoute() {
                    const { origin, destination } = this.state;

                    if (!origin || !destination) {
                        this.showError('Selecciona origen y destino');
                        return;
                    }

                    const directDist = this.haversine(
                        origin.lat, origin.lng,
                        destination.lat, destination.lng
                    );

                    if (directDist < 100) {
                        this.showError('El destino está muy cerca. Camina directamente.');
                        return;
                    }

                    this.state.isLoading = true;
                    this.renderLoading();

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                        const response = await fetch('/api/routing/shortest-path', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                origen: {
                                    latitud: origin.lat,
                                    longitud: origin.lng
                                },
                                destino: {
                                    latitud: destination.lat,
                                    longitud: destination.lng
                                },
                                opciones: {
                                    max_trasbordos: this.config.maxTrasbordos,
                                    max_caminata_m: this.config.maxCaminataM
                                }
                            })
                        });

                        const result = await response.json();

                        if (result.success && result.data) {
                            this.state.routeData = result.data;
                            this.renderRoute(result.data);
                            this.renderItinerary(result.data);
                        } else {
                            this.showError(result.message || 'No se encontró ruta disponible');
                        }

                    } catch (error) {
                        console.error('Error al calcular ruta:', error);
                        this.showError('Error de conexión. Intenta nuevamente.');
                    } finally {
                        this.state.isLoading = false;
                    }
                },

                renderOriginMarker() {
                    if (this.layers.originMarker) {
                        map.removeLayer(this.layers.originMarker);
                    }

                    const icon = L.divIcon({
                        className: 'custom-user-icon',
                        html: '<div class="user-pin-glow"></div>',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    this.layers.originMarker = L.marker(
                        [this.state.origin.lat, this.state.origin.lng],
                        { icon }
                    ).addTo(map).bindPopup('<strong>Tu ubicación</strong>');
                },

                renderDestinationMarker() {
                    if (this.layers.destinationMarker) {
                        map.removeLayer(this.layers.destinationMarker);
                    }

                    const icon = L.divIcon({
                        className: 'destination-marker',
                        html: `<div style="background: #ef4444; width: 28px; height: 28px; border-radius: 50%; border: 3px solid #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 15px rgba(239, 68, 68, 0.6);">
                                 <i class="fa-solid fa-flag text-white" style="font-size: 12px;"></i>
                               </div>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14]
                    });

                    this.layers.destinationMarker = L.marker(
                        [this.state.destination.lat, this.state.destination.lng],
                        { icon }
                    ).addTo(map).bindPopup('<strong>Destino</strong>');
                },

                renderRoute(data) {
                    this.clearRoute();

                    const { segmentos } = data;
                    const bounds = L.latLngBounds();

                    segmentos.forEach((segmento, index) => {
                        if (segmento.tipo === 'caminata') {
                            this.renderWalkingSegment(segmento, index, bounds);
                        } else if (segmento.tipo === 'transporte') {
                            this.renderTransportSegment(segmento, index, bounds);
                        }
                    });

                    if (bounds.isValid()) {
                        map.fitBounds(bounds, { padding: [50, 50] });
                    }
                },

                renderWalkingSegment(segmento, index, bounds) {
                    const coords = [
                        [segmento.desde.latitud, segmento.desde.longitud],
                        [segmento.hasta.latitud, segmento.hasta.longitud]
                    ];

                    const polyline = L.polyline(coords, {
                        color: '#6b7280',
                        weight: 3,
                        opacity: 0.8,
                        dashArray: '5, 10'
                    }).addTo(map);

                    this.layers.routePolylines.push(polyline);

                    coords.forEach(c => bounds.extend(c));

                    polyline.bindPopup(`
                        <strong>🚶 Caminata</strong><br>
                        ${segmento.distancia_m} m • ${this.formatTime(segmento.tiempo_s)}<br>
                        <small>${segmento.descripcion}</small>
                    `);
                },

                renderTransportSegment(segmento, index, bounds) {
                    const coords = segmento.coordenadas_ruta.map(p => [p.latitud, p.longitud]);

                    if (coords.length === 0) return;

                    const polyline = L.polyline(coords, {
                        color: segmento.ruta_color,
                        weight: 6,
                        opacity: 0.9
                    }).addTo(map);

                    this.layers.routePolylines.push(polyline);

                    coords.forEach(c => bounds.extend(c));

                    polyline.bindPopup(`
                        <strong>🚌 ${segmento.ruta_nombre}</strong><br>
                        ${segmento.distancia_m} m • ${this.formatTime(segmento.tiempo_s)}<br>
                        ${segmento.paradas_intermedias} paradas<br>
                        <small>${segmento.descripcion}</small>
                    `);

                    if (index < this.state.routeData.segmentos.length - 1) {
                        const lastCoord = coords[coords.length - 1];
                        this.renderTransferMarker(lastCoord, index);
                    }
                },

                renderTransferMarker(coords, transferNumber) {
                    const icon = L.divIcon({
                        className: 'transfer-marker',
                        html: `<div style="background: #f59e0b; width: 24px; height: 24px; border-radius: 50%; border: 2px solid #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 10px rgba(245, 158, 11, 0.6);">
                                 <span style="color: #000; font-weight: bold; font-size: 12px;">${transferNumber}</span>
                               </div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    const marker = L.marker(coords, { icon }).addTo(map);
                    marker.bindPopup(`<strong>Trasbordo ${transferNumber}</strong>`);

                    this.layers.transferMarkers.push(marker);
                },

                renderItinerary(data) {
                    const { segmentos, tiempo_total_estimado_s, distancia_total_m, numero_trasbordos } = data;

                    const container = document.getElementById('itinerary-container');

                    let html = `
                        <div class="glass-card p-3 mb-3">
                            <div class="row g-0 text-center">
                                <div class="col-4">
                                    <small class="text-muted-custom d-block fs-8">TIEMPO</small>
                                    <span class="text-success fw-bold fs-5">${this.formatTime(tiempo_total_estimado_s)}</span>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted-custom d-block fs-8">DISTANCIA</small>
                                    <span class="text-white fw-bold fs-5">${(distancia_total_m / 1000).toFixed(1)} km</span>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted-custom d-block fs-8">TRASBORDOS</small>
                                    <span class="text-warning fw-bold fs-5">${numero_trasbordos}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                    `;

                    segmentos.forEach((seg, i) => {
                        const icon = seg.tipo === 'caminata' ? '🚶' : '🚌';
                        const bgColor = seg.tipo === 'caminata' ? 'rgba(107, 114, 128, 0.1)' : `${seg.ruta_color}15`;
                        const borderColor = seg.tipo === 'caminata' ? '#6b7280' : seg.ruta_color;

                        html += `
                            <div class="glass-card p-3" style="background: ${bgColor}; border-left: 3px solid ${borderColor};">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-center gap-2">
                                        <span style="font-size: 20px;">${icon}</span>
                                        <div>
                                            <h4 class="fs-7 text-white mb-1 fw-semibold">
                                                ${seg.tipo === 'transporte' ? seg.ruta_nombre : 'Caminata'}
                                            </h4>
                                            <small class="text-muted-custom fs-8">${seg.descripcion}</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-success fw-bold fs-7 d-block">${this.formatTime(seg.tiempo_s)}</span>
                                        <small class="text-muted-custom fs-9">${seg.distancia_m} m</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                    container.innerHTML = html;
                },

                renderLoading() {
                    const container = document.getElementById('itinerary-container');
                    container.innerHTML = `
                        <div class="text-center py-4">
                            <div class="spinner-border text-success mb-2" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="text-muted-custom fs-8 mb-0">Calculando ruta más rápida...</p>
                        </div>
                    `;
                },

                showError(message) {
                    const container = document.getElementById('itinerary-container');
                    container.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fa-solid fa-exclamation-triangle text-warning fs-1 mb-2"></i>
                            <p class="text-white fs-8 mb-0">${message}</p>
                        </div>
                    `;
                },

                clearRoute() {
                    this.layers.routePolylines.forEach(p => map.removeLayer(p));
                    this.layers.transferMarkers.forEach(m => map.removeLayer(m));
                    this.layers.routePolylines = [];
                    this.layers.transferMarkers = [];
                },

                updateUI() {
                    const label = document.getElementById('destination-label');
                    if (this.state.destination && label) {
                        label.textContent = `${this.state.destination.lat.toFixed(4)}, ${this.state.destination.lng.toFixed(4)}`;
                    }
                },

                haversine(lat1, lng1, lat2, lng2) {
                    const R = 6371000;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLng = (lng2 - lng1) * Math.PI / 180;
                    const a = Math.sin(dLat/2)**2 +
                              Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
                              Math.sin(dLng/2)**2;
                    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                },

                formatTime(seconds) {
                    if (seconds < 60) return `${seconds}s`;
                    const mins = Math.round(seconds / 60);
                    if (mins < 60) return `${mins} min`;
                    const hours = Math.floor(mins / 60);
                    const remainMins = mins % 60;
                    return `${hours}h ${remainMins}min`;
                },
            };

            RoutePlanner.init();
        }
    });
</script>
