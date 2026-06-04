<?php

namespace App\Services\Routing;

use App\Models\Ruta;

class RouteFormatter
{
    private GraphBuilder $graph;

    public function __construct(GraphBuilder $graph)
    {
        $this->graph = $graph;
    }

    public function format(?array $result, float $originLat, float $originLng, float $destLat, float $destLng): array
    {
        if ($result === null) {
            return [
                'success' => true,
                'data' => null,
                'message' => 'No se encontro una ruta con los parametros especificados. Intenta aumentar max_trasbordos o max_caminata_m.',
            ];
        }

        $segments = $this->buildSegments($result['path'], $originLat, $originLng, $destLat, $destLng);

        return [
            'success' => true,
            'data' => [
                'tiempo_total_estimado_s' => $result['totalTime'],
                'distancia_total_m' => (int) round($result['totalDistance']),
                'numero_trasbordos' => max(0, $result['transfers'] - 1),
                'segmentos' => $segments,
            ],
        ];
    }

    private function buildSegments(array $path, float $originLat, float $originLng, float $destLat, float $destLng): array
    {
        $segments = [];
        $currentTransport = null;

        foreach ($path as $step) {
            if ($step['type'] === 'walk') {
                if ($currentTransport !== null) {
                    $segments[] = $currentTransport;
                    $currentTransport = null;
                }

                if ($step['distance_m'] < 1) {
                    continue;
                }

                $fromNode = $this->graph->nodes[$step['from']] ?? null;
                $toNode = $this->graph->nodes[$step['to']] ?? null;

                $fromLat = $fromNode ? $fromNode['lat'] : $originLat;
                $fromLng = $fromNode ? $fromNode['lng'] : $originLng;
                $toLat = $toNode ? $toNode['lat'] : $destLat;
                $toLng = $toNode ? $toNode['lng'] : $destLng;

                $segments[] = [
                    'tipo' => 'caminata',
                    'descripcion' => $this->walkDescription($step, $fromNode, $toNode),
                    'distancia_m' => (int) round($step['distance_m']),
                    'tiempo_s' => $step['time_s'],
                    'desde' => [
                        'latitud' => $fromLat,
                        'longitud' => $fromLng,
                    ],
                    'hasta' => [
                        'latitud' => $toLat,
                        'longitud' => $toLng,
                    ],
                ];
            } elseif ($step['type'] === 'board') {
                if ($currentTransport !== null) {
                    $segments[] = $currentTransport;
                }

                $ruta = Ruta::find($step['ruta_id']);
                $hex = $this->extractHexColor($ruta->color);
                $name = $this->extractRouteName($ruta->color);
                $boardNode = $this->graph->nodes[$step['at_node']] ?? null;

                $currentTransport = [
                    'tipo' => 'transporte',
                    'ruta_id' => $ruta->id,
                    'ruta_color' => $hex,
                    'ruta_nombre' => $name,
                    'descripcion' => '',
                    'paradas_intermedias' => 0,
                    'distancia_m' => 0,
                    'tiempo_s' => $step['wait_time_s'],
                    'desde_nodo' => $step['at_node'],
                    'hasta_nodo' => null,
                    'coordenadas_ruta' => [],
                ];

                if ($boardNode) {
                    $currentTransport['coordenadas_ruta'][] = [
                        'latitud' => $boardNode['lat'],
                        'longitud' => $boardNode['lng'],
                    ];
                }
            } elseif ($step['type'] === 'route') {
                if ($currentTransport === null) {
                    $ruta = Ruta::find($step['ruta_id']);
                    $hex = $this->extractHexColor($ruta->color);
                    $name = $this->extractRouteName($ruta->color);

                    $currentTransport = [
                        'tipo' => 'transporte',
                        'ruta_id' => $ruta->id,
                        'ruta_color' => $hex,
                        'ruta_nombre' => $name,
                        'descripcion' => '',
                        'paradas_intermedias' => 0,
                        'distancia_m' => 0,
                        'tiempo_s' => 0,
                        'desde_nodo' => null,
                        'hasta_nodo' => null,
                        'coordenadas_ruta' => [],
                    ];
                }

                $currentTransport['paradas_intermedias']++;
                $currentTransport['distancia_m'] += $step['distance_m'];
                $currentTransport['tiempo_s'] += $step['time_s'];
                $currentTransport['hasta_nodo'] = $step['to'];

                $toNode = $this->graph->nodes[$step['to']] ?? null;
                if ($toNode) {
                    $currentTransport['coordenadas_ruta'][] = [
                        'latitud' => $toNode['lat'],
                        'longitud' => $toNode['lng'],
                    ];
                }
            }
        }

        if ($currentTransport !== null) {
            $segments[] = $currentTransport;
        }

        foreach ($segments as &$seg) {
            if ($seg['tipo'] === 'transporte') {
                $seg['distancia_m'] = (int) round($seg['distancia_m']);
                unset($seg['desde_nodo'], $seg['hasta_nodo']);
                $seg['descripcion'] = "Toma {$seg['ruta_nombre']} ({$seg['paradas_intermedias']} paradas)";
            }
        }

        return $segments;
    }

    private function walkDescription(array $step, ?array $fromNode, ?array $toNode): string
    {
        $dist = (int) round($step['distance_m']);

        if ($step['from'] === 'origin') {
            return "Camina {$dist} m hacia la parada mas cercana";
        }

        if ($step['to'] === 'dest') {
            return "Camina {$dist} m hacia tu destino";
        }

        return "Camina {$dist} m (trasbordo)";
    }

    private function extractHexColor(string $colorString): string
    {
        $parts = explode(' - ', $colorString);
        return $parts[0] ?? '#10b981';
    }

    private function extractRouteName(string $colorString): string
    {
        $parts = explode(' - ', $colorString);
        $namePart = implode(' - ', array_slice($parts, 1));

        if (preg_match('/\((\d+)\)/', $namePart, $matches)) {
            return trim(str_replace("({$matches[1]})", '', $namePart));
        }

        return trim($namePart);
    }
}
