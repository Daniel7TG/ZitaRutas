@extends('layouts.app')

@section('content')
    @php
        // Detectamos la pantalla activa de forma dinámica a través de los Query Parameters de Laravel (PHP)
        $activeScreen = request()->query('screen', 'welcome');
        $activeRouteId = request()->query('route_id', '1'); // Por defecto, la primera ruta
        $activeUnit = request()->query('unit', 'AM3-A');

        // Helper en PHP para parsear los datos unificados en la columna 'color' de nuestra BD
        if (!function_exists('parseRouteData')) {
            function parseRouteData($ruta)
            {
                $colorString = $ruta->color;
                $parts = explode(' - ', $colorString);

                // Extraer color hexadecimal
                $hex = $parts[0] ?? '#10b981';

                // Reconstruir el nombre de la ruta
                $namePart = implode(' - ', array_slice($parts, 1));

                $routeCode = '';
                // Extraer el código numérico original de la ruta que está entre paréntesis (ej. 15257790)
                if (preg_match('/\((\d+)\)/', $namePart, $matches)) {
                    $routeCode = $matches[1];
                    $name = trim(str_replace("({$routeCode})", '', $namePart));
                } else {
                    $name = trim($namePart);
                    $routeCode = $ruta->id;
                }

                // Fallback si no hay nombre
                if (empty($name)) {
                    $name = 'Ruta ' . $routeCode;
                }

                // Generar una abreviación corta de 3-4 caracteres para la insignia (ej. AM3, RA2, AZ11)
                $shortName = '';
                if (preg_match('/([a-zA-Z]+[0-9]+)/i', $name, $matches)) {
                    $shortName = strtoupper($matches[1]);
                } else {
                    // Tomar las iniciales de las palabras
                    $words = explode(' ', $name);
                    if (count($words) >= 2) {
                        $shortName = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                    } else {
                        $shortName = 'R' . substr($routeCode, -2);
                    }
                }

                return [
                    'hex' => $hex,
                    'name' => $name,
                    'code' => $routeCode,
                    'short' => substr($shortName, 0, 5),
                ];
            }
        }

        // Buscamos la ruta activa seleccionada mediante Eloquent
        $selectedRuta = $rutas->firstWhere('id', $activeRouteId) ?? $rutas->first();
        $selectedRutaInfo = $selectedRuta ? parseRouteData($selectedRuta) : null;

        // Pre-computar datos para JavaScript y evitar errores de compilación de Blade con closures complejas
        $realRoutesData = [];
        $selectedRouteCoordsData = [];
        $focusedRouteId = request()->query('focused_route_id', null);

        if (in_array($activeScreen, ['routes', 'tracking', 'route-detail'])) {
            if ($focusedRouteId) {
                $focusedRuta = $rutas->firstWhere('id', $focusedRouteId);
                if ($focusedRuta) {
                    $realRoutesData = [
                        [
                            'id' => $focusedRuta->id,
                            'color' => parseRouteData($focusedRuta)['hex'],
                            'name' => parseRouteData($focusedRuta)['name'],
                            'short' => parseRouteData($focusedRuta)['short'],
                            'coordinates' => $focusedRuta->relationLoaded('puntosNavegacion')
                                ? $focusedRuta->puntosNavegacion->map(
                                    fn($p) => [(float) $p->latitud, (float) $p->longitud],
                                )
                                : collect(),
                        ],
                    ];
                }
            } else {
                $realRoutesData = $rutas
                    //->random(min($rutas->count(), 8))
                    ->take(149)
                    ->map(function ($r) {
                        $info = parseRouteData($r);
                        $coords = $r->relationLoaded('puntosNavegacion')
                            ? $r->puntosNavegacion->map(fn($p) => [(float) $p->latitud, (float) $p->longitud])
                            : collect();
                        return [
                            'id' => $r->id,
                            'color' => $info['hex'],
                            'name' => $info['name'],
                            'short' => $info['short'],
                            'coordinates' => $coords,
                        ];
                    })
                    ->toArray();
            }

            $selectedRouteCoordsData =
                $selectedRuta && $selectedRuta->relationLoaded('puntosNavegacion')
                    ? $selectedRuta->puntosNavegacion
                        ->map(fn($p) => [(float) $p->latitud, (float) $p->longitud])
                        ->toArray()
                    : [];
        }
    @endphp

    @if ($activeScreen == 'welcome')
        @include('screens.welcome')
    @endif

    @if ($activeScreen == 'permissions')
        @include('screens.permissions')
    @endif

    @if ($activeScreen == 'routes')
        @include('screens.routes')
    @endif

    @if ($activeScreen == 'route-detail')
        @include('screens.route-detail')
    @endif

    @if ($activeScreen == 'tracking')
        @include('screens.tracking')
    @endif

    @if ($activeScreen == 'favorites')
        @include('screens.favorites')
    @endif

    @if ($activeScreen == 'routes-list')
        @include('screens.routes-list')
    @endif
@endsection

@push('scripts')
    @include('partials.map-scripts')
    @include('partials.drag-scripts')
@endpush
