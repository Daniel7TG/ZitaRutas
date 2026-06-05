<?php

namespace App\Services\Routing;

use App\Models\PuntoNavegacion;

class GraphBuilder
{
    const MAX_WALKING_DISTANCE_M = 500;

    public array $nodes = [];
    public array $edges = [];

    public function build(float $originLat, float $originLng, float $destLat, float $destLng): self
    {
        $distanceM = GeoUtils::haversine($originLat, $originLng, $destLat, $destLng);
        
        if ($distanceM < 2000) {
            $buffer = 0.015;
        } elseif ($distanceM < 5000) {
            $buffer = 0.03;
        } else {
            $buffer = 0.05;
        }

        $minLat = min($originLat, $destLat) - $buffer;
        $maxLat = max($originLat, $destLat) + $buffer;
        $minLng = min($originLng, $destLng) - $buffer;
        $maxLng = max($originLng, $destLng) + $buffer;

        $puntos = PuntoNavegacion::with('ruta')
            ->whereBetween('latitud', [$minLat, $maxLat])
            ->whereBetween('longitud', [$minLng, $maxLng])
            ->orderBy('ruta_id')
            ->orderBy('id')
            ->get();

        $rutasInfo = [];
        foreach ($puntos as $punto) {
            if (!isset($rutasInfo[$punto->ruta_id])) {
                $rutasInfo[$punto->ruta_id] = [
                    'color' => $this->extractHexColor($punto->ruta->color),
                    'name' => $this->extractRouteName($punto->ruta->color),
                ];
            }

            $this->nodes[$punto->id] = [
                'id' => $punto->id,
                'lat' => (float) $punto->latitud,
                'lng' => (float) $punto->longitud,
                'ruta_id' => $punto->ruta_id,
                'ruta_color' => $rutasInfo[$punto->ruta_id]['color'],
                'ruta_name' => $rutasInfo[$punto->ruta_id]['name'],
            ];
        }

        $this->buildRouteEdges($puntos);
        $this->buildWalkingEdges();

        $this->nodes['origin'] = [
            'id' => 'origin',
            'lat' => $originLat,
            'lng' => $originLng,
            'ruta_id' => null,
            'ruta_color' => null,
            'ruta_name' => null,
        ];

        $this->nodes['dest'] = [
            'id' => 'dest',
            'lat' => $destLat,
            'lng' => $destLng,
            'ruta_id' => null,
            'ruta_color' => null,
            'ruta_name' => null,
        ];

        $this->connectVirtualNode('origin', $originLat, $originLng, 'from');
        $this->connectVirtualNode('dest', $destLat, $destLng, 'to');

        return $this;
    }

    private function buildRouteEdges($puntos): void
    {
        $puntosPorRuta = $puntos->groupBy('ruta_id');

        foreach ($puntosPorRuta as $rutaId => $puntosRuta) {
            $puntosOrdenados = $puntosRuta->sortBy('id')->values();

            for ($i = 0; $i < count($puntosOrdenados) - 1; $i++) {
                $p1 = $puntosOrdenados[$i];
                $p2 = $puntosOrdenados[$i + 1];

                $dist = GeoUtils::haversine(
                    (float) $p1->latitud, (float) $p1->longitud,
                    (float) $p2->latitud, (float) $p2->longitud
                );

                $time = GeoUtils::busTime($dist);

                $this->addEdge($p1->id, $p2->id, 'route', $rutaId, $dist, $time);
            }
        }
    }

    private function buildWalkingEdges(): void
    {
        $gridSize = 0.008;
        $grid = [];

        foreach ($this->nodes as $id => $node) {
            if (!is_int($id)) continue;

            $cellLat = (int) floor($node['lat'] / $gridSize);
            $cellLng = (int) floor($node['lng'] / $gridSize);
            $key = "{$cellLat},{$cellLng}";

            if (!isset($grid[$key])) {
                $grid[$key] = [];
            }
            $grid[$key][] = $id;
        }

        $maxWalkingEdgesPerNode = 2;
        $walkingEdgesCount = [];

        foreach ($grid as $cellKey => $cellNodes) {
            [$cellLat, $cellLng] = explode(',', $cellKey);
            $cellLat = (int) $cellLat;
            $cellLng = (int) $cellLng;

            $neighborNodes = $cellNodes;
            for ($dLat = -1; $dLat <= 1; $dLat++) {
                for ($dLng = -1; $dLng <= 1; $dLng++) {
                    if ($dLat === 0 && $dLng === 0) continue;
                    $neighborKey = ($cellLat + $dLat) . ',' . ($cellLng + $dLng);
                    if (isset($grid[$neighborKey])) {
                        $neighborNodes = array_merge($neighborNodes, $grid[$neighborKey]);
                    }
                }
            }

            foreach ($cellNodes as $id1) {
                if (!isset($walkingEdgesCount[$id1])) {
                    $walkingEdgesCount[$id1] = 0;
                }
                
                if ($walkingEdgesCount[$id1] >= $maxWalkingEdgesPerNode) {
                    continue;
                }

                $node1 = $this->nodes[$id1];
                $candidates = [];

                foreach ($neighborNodes as $id2) {
                    if ($id1 === $id2) continue;

                    $node2 = $this->nodes[$id2];

                    if ($node1['ruta_id'] === $node2['ruta_id']) continue;

                    $dist = GeoUtils::haversine($node1['lat'], $node1['lng'], $node2['lat'], $node2['lng']);

                    if ($dist <= self::MAX_WALKING_DISTANCE_M) {
                        $candidates[] = ['id' => $id2, 'distance' => $dist];
                    }
                }

                usort($candidates, fn($a, $b) => $a['distance'] <=> $b['distance']);

                $candidates = array_slice($candidates, 0, $maxWalkingEdgesPerNode - $walkingEdgesCount[$id1]);

                foreach ($candidates as $candidate) {
                    $id2 = $candidate['id'];
                    $dist = $candidate['distance'];
                    $time = GeoUtils::walkingTime($dist);
                    
                    $this->addEdge($id1, $id2, 'walk', null, $dist, $time);
                    $this->addEdge($id2, $id1, 'walk', null, $dist, $time);
                    
                    $walkingEdgesCount[$id1]++;
                    if (!isset($walkingEdgesCount[$id2])) {
                        $walkingEdgesCount[$id2] = 0;
                    }
                    $walkingEdgesCount[$id2]++;
                }
            }
        }
    }

    private function addEdge($from, $to, string $type, ?int $rutaId, float $distance, int $time): void
    {
        if (!isset($this->edges[$from])) {
            $this->edges[$from] = [];
        }

        $this->edges[$from][] = [
            'to' => $to,
            'type' => $type,
            'ruta_id' => $rutaId,
            'distance_m' => $distance,
            'time_s' => $time,
        ];
    }

    private function connectVirtualNode(string $virtualId, float $lat, float $lng, string $direction): void
    {
        foreach ($this->nodes as $id => $node) {
            if (!is_int($id)) continue;

            $dist = GeoUtils::haversine($lat, $lng, $node['lat'], $node['lng']);

            if ($dist <= self::MAX_WALKING_DISTANCE_M) {
                $time = GeoUtils::walkingTime($dist);
                if ($direction === 'from') {
                    $this->addEdge($virtualId, $id, 'walk', null, $dist, $time);
                } else {
                    $this->addEdge($id, $virtualId, 'walk', null, $dist, $time);
                }
            }
        }
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
