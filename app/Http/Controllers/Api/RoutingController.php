<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoutingRequest;
use App\Services\Routing\GraphBuilder;
use App\Services\Routing\RouteFormatter;
use App\Services\Routing\ShortestPathEngine;
use Illuminate\Http\JsonResponse;

class RoutingController extends Controller
{
    public function shortestPath(StoreRoutingRequest $request): JsonResponse
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);
        
        $validated = $request->validated();

        $originLat = (float) $validated['origen']['latitud'];
        $originLng = (float) $validated['origen']['longitud'];
        $destLat = (float) $validated['destino']['latitud'];
        $destLng = (float) $validated['destino']['longitud'];

        $maxTransfers = $validated['opciones']['max_trasbordos'] ?? 3;

        $graph = new GraphBuilder();
        $graph->build($originLat, $originLng, $destLat, $destLng);

        $engine = new ShortestPathEngine($graph, $maxTransfers);
        $result = $engine->find();

        $formatter = new RouteFormatter($graph);
        $response = $formatter->format($result, $originLat, $originLng, $destLat, $destLng);

        return response()->json($response);
    }
}
