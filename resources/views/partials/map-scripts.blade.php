<script>
    document.addEventListener("DOMContentLoaded", function() {
        const activeScreen = "{{ $activeScreen }}";

        if (activeScreen === 'routes' || activeScreen === 'tracking') {
            const zitacuaroCoords = [19.4357, -100.3571];

            const map = L.map('leaflet-map', {
                center: zitacuaroCoords,
                zoom: 14,
                zoomControl: false,
                attributionControl: true
            });
            window.map = map;

            const tileAttribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>';
            const tileOptions = {
                attribution: tileAttribution,
                subdomains: 'abcd',
                maxZoom: 20
            };

            const darkTile = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', tileOptions);
            const lightTile = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', tileOptions);

            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            let activeTile = currentTheme === 'light' ? lightTile : darkTile;
            activeTile.addTo(map);

            document.addEventListener('themeChanged', function(e) {
                const newTheme = e.detail.theme;
                map.removeLayer(activeTile);
                activeTile = newTheme === 'light' ? lightTile : darkTile;
                activeTile.addTo(map);
            });

            if (activeScreen === 'routes') {
                const realRoutes = @json($realRoutesData);
                const isFocused = @json((bool) $focusedRouteId);
                let routeBounds = L.latLngBounds();

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
                    }
                });

                if (!isFocused) {
                    map.setView(zitacuaroCoords, 14);
                } else if (realRoutes.length > 0 && routeBounds.isValid()) {
                    map.fitBounds(routeBounds, {
                        padding: [50, 50]
                    });
                }

            } else if (activeScreen === 'tracking') {
                const selectedRouteCoords = @json($selectedRouteCoordsData);
                const selectedRouteColor = "{{ $selectedRutaInfo['hex'] ?? '#10b981' }}";

                if (selectedRouteCoords.length > 0) {
                    const polyline = L.polyline(selectedRouteCoords, {
                        color: selectedRouteColor,
                        weight: 6,
                        opacity: 0.8
                    }).addTo(map);

                    map.fitBounds(polyline.getBounds(), {
                        padding: [30, 30]
                    });
                }
            }

            const wsHost = window.location.hostname || "127.0.0.1";
            const wsPort = "8080";
            const socket = new WebSocket(`ws://${wsHost}:${wsPort}`);

            socket.onopen = function() {
                console.log("WebSocket conectado");
            };

            socket.onmessage = function(event) {
                try {
                    const message = JSON.parse(event.data);
                    if (message.type === 'location_update') {
                        const { latitud, longitud } = message;

                        if (activeScreen === 'tracking') {
                            map.panTo([latitud, longitud]);
                        }
                    }
                } catch (e) {
                    console.error("Error WebSocket message:", e);
                }
            };

            socket.onerror = function(error) {
                console.warn("WebSocket error:", error);
            };

            socket.onclose = function() {
                console.log("WebSocket cerrado");
            };
        }
    });
</script>
