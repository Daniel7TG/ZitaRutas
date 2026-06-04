<?php

namespace App\Services\Routing;

use SplPriorityQueue;

class ShortestPathEngine
{
    private GraphBuilder $graph;
    private int $maxTransfers;

    public function __construct(GraphBuilder $graph, int $maxTransfers = 3)
    {
        $this->graph = $graph;
        $this->maxTransfers = $maxTransfers;
    }

    public function find(): ?array
    {
        $pq = new SplPriorityQueue();
        $pq->setExtractFlags(SplPriorityQueue::EXTR_DATA);

        $initial = [
            'node' => 'origin',
            'currentRoute' => null,
            'transfers' => 0,
            'totalTime' => 0,
            'totalDistance' => 0,
            'path' => [],
        ];

        $pq->insert($initial, -$initial['totalTime']);

        $visited = [];

        while (!$pq->isEmpty()) {
            $state = $pq->extract();

            if ($state['node'] === 'dest') {
                return [
                    'path' => $state['path'],
                    'totalTime' => $state['totalTime'],
                    'totalDistance' => $state['totalDistance'],
                    'transfers' => $state['transfers'],
                ];
            }

            $key = "{$state['node']}|{$state['currentRoute']}|{$state['transfers']}";
            if (isset($visited[$key]) && $visited[$key] <= $state['totalTime']) {
                continue;
            }
            $visited[$key] = $state['totalTime'];

            $edges = $this->graph->edges[$state['node']] ?? [];

            foreach ($edges as $edge) {
                if ($edge['type'] === 'route') {
                    $this->expandRouteEdge($state, $edge, $pq);
                } elseif ($edge['type'] === 'walk') {
                    $this->expandWalkEdge($state, $edge, $pq);
                }
            }
        }

        return null;
    }

    private function expandRouteEdge(array $state, array $edge, SplPriorityQueue $pq): void
    {
        if ($state['currentRoute'] === $edge['ruta_id']) {
            $newState = $state;
            $newState['node'] = $edge['to'];
            $newState['totalTime'] += $edge['time_s'];
            $newState['totalDistance'] += $edge['distance_m'];
            $newState['path'][] = [
                'type' => 'route',
                'ruta_id' => $edge['ruta_id'],
                'to' => $edge['to'],
                'distance_m' => $edge['distance_m'],
                'time_s' => $edge['time_s'],
            ];
            $pq->insert($newState, -$newState['totalTime']);
        } else {
            if ($state['transfers'] >= $this->maxTransfers) {
                return;
            }

            $newState = $state;
            $newState['node'] = $edge['to'];
            $newState['currentRoute'] = $edge['ruta_id'];
            $newState['transfers'] += 1;
            $newState['totalTime'] += GeoUtils::WAIT_TIME_S + $edge['time_s'];
            $newState['totalDistance'] += $edge['distance_m'];
            $newState['path'][] = [
                'type' => 'board',
                'ruta_id' => $edge['ruta_id'],
                'at_node' => $state['node'],
                'wait_time_s' => GeoUtils::WAIT_TIME_S,
            ];
            $newState['path'][] = [
                'type' => 'route',
                'ruta_id' => $edge['ruta_id'],
                'to' => $edge['to'],
                'distance_m' => $edge['distance_m'],
                'time_s' => $edge['time_s'],
            ];
            $pq->insert($newState, -$newState['totalTime']);
        }
    }

    private function expandWalkEdge(array $state, array $edge, SplPriorityQueue $pq): void
    {
        $newState = $state;
        $newState['node'] = $edge['to'];
        $newState['currentRoute'] = null;
        $newState['totalTime'] += $edge['time_s'];
        $newState['totalDistance'] += $edge['distance_m'];
        $newState['path'][] = [
            'type' => 'walk',
            'from' => $state['node'],
            'to' => $edge['to'],
            'distance_m' => $edge['distance_m'],
            'time_s' => $edge['time_s'],
        ];
        $pq->insert($newState, -$newState['totalTime']);
    }
}
