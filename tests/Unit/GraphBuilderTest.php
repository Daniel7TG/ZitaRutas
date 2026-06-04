<?php

namespace Tests\Unit;

use App\Models\PuntoNavegacion;
use App\Models\Ruta;
use App\Services\Routing\GraphBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraphBuilderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que el grafo se construye con nodos desde puntos_navegacion.
     */
    public function test_graph_builder_creates_nodes_from_puntos_navegacion(): void
    {
        $ruta = Ruta::create(['color' => '#FF0000 - Ruta Roja (12345)']);

        PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4360000,
            'longitud' => -100.3580000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.4360, -100.3580);

        $this->assertArrayHasKey('origin', $builder->nodes);
        $this->assertArrayHasKey('dest', $builder->nodes);
        $this->assertGreaterThanOrEqual(2, count($builder->nodes));
    }

    /**
     * Test que se crean aristas de ruta entre puntos consecutivos.
     */
    public function test_graph_builder_creates_route_edges(): void
    {
        $ruta = Ruta::create(['color' => '#FF0000 - Ruta Roja (12345)']);

        $p1 = PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        $p2 = PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4360000,
            'longitud' => -100.3580000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.4360, -100.3580);

        $this->assertArrayHasKey($p1->id, $builder->edges);
        $this->assertNotEmpty($builder->edges[$p1->id]);

        $routeEdges = array_filter($builder->edges[$p1->id], fn($e) => $e['type'] === 'route');
        $this->assertNotEmpty($routeEdges);
        $this->assertEquals($ruta->id, $routeEdges[array_key_first($routeEdges)]['ruta_id']);
    }

    /**
     * Test que se crean aristas de caminata entre puntos cercanos de distintas rutas.
     */
    public function test_graph_builder_creates_walking_edges_between_nearby_routes(): void
    {
        $ruta1 = Ruta::create(['color' => '#FF0000 - Ruta Roja (12345)']);
        $ruta2 = Ruta::create(['color' => '#0000FF - Ruta Azul (67890)']);

        $p1 = PuntoNavegacion::create([
            'ruta_id' => $ruta1->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        $p2 = PuntoNavegacion::create([
            'ruta_id' => $ruta2->id,
            'latitud' => 19.4358000,
            'longitud' => -100.3572000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.4358, -100.3572);

        $this->assertArrayHasKey($p1->id, $builder->edges);

        $walkingEdges = array_filter($builder->edges[$p1->id], fn($e) => $e['type'] === 'walk');
        $this->assertNotEmpty($walkingEdges);
    }

    /**
     * Test que NO se crean aristas de caminata entre puntos lejanos.
     */
    public function test_graph_builder_does_not_create_walking_edges_for_far_points(): void
    {
        $ruta1 = Ruta::create(['color' => '#FF0000 - Ruta Roja (12345)']);
        $ruta2 = Ruta::create(['color' => '#0000FF - Ruta Azul (67890)']);

        $p1 = PuntoNavegacion::create([
            'ruta_id' => $ruta1->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        $p2 = PuntoNavegacion::create([
            'ruta_id' => $ruta2->id,
            'latitud' => 19.5000000,
            'longitud' => -100.4000000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.5000, -100.4000);

        if (isset($builder->edges[$p1->id])) {
            $walkingEdges = array_filter($builder->edges[$p1->id], fn($e) => $e['type'] === 'walk');
            $this->assertEmpty($walkingEdges);
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * Test que los nodos origin y dest se conectan a todos los puntos cercanos dentro de 500m.
     */
    public function test_origin_and_dest_connect_to_all_nearby_points(): void
    {
        $ruta1 = Ruta::create(['color' => '#FF0000 - Ruta Roja (12345)']);
        $ruta2 = Ruta::create(['color' => '#0000FF - Ruta Azul (67890)']);

        $p1 = PuntoNavegacion::create([
            'ruta_id' => $ruta1->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        $p2 = PuntoNavegacion::create([
            'ruta_id' => $ruta2->id,
            'latitud' => 19.4358000,
            'longitud' => -100.3572000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.4358, -100.3572);

        // Origin debe tener edges walk a ambos puntos
        $this->assertArrayHasKey('origin', $builder->edges);
        $originWalks = array_filter($builder->edges['origin'], fn($e) => $e['type'] === 'walk');
        $this->assertCount(2, $originWalks);

        // Dest debe ser alcanzable desde ambos puntos
        $destEdges = [];
        foreach ($builder->edges as $from => $edges) {
            foreach ($edges as $edge) {
                if ($edge['to'] === 'dest' && $edge['type'] === 'walk') {
                    $destEdges[] = $edge;
                }
            }
        }
        $this->assertCount(2, $destEdges);
    }

    /**
     * Test que NO se crean edges cuando origin y dest están lejos de todos los puntos.
     */
    public function test_no_edges_created_when_origin_and_dest_far_from_all_points(): void
    {
        $ruta = Ruta::create(['color' => '#FF0000 - Ruta Roja (12345)']);

        PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        // Origin y dest a ~16km de los puntos de navegación
        $builder = new GraphBuilder();
        $builder->build(19.5000, -100.5000, 19.6000, -100.6000);

        // Origin no debe tener edges
        $this->assertArrayNotHasKey('origin', $builder->edges);

        // Ningún nodo debe tener edge hacia dest
        $destEdges = [];
        foreach ($builder->edges as $from => $edges) {
            foreach ($edges as $edge) {
                if ($edge['to'] === 'dest') {
                    $destEdges[] = $edge;
                }
            }
        }
        $this->assertEmpty($destEdges);
    }

    /**
     * Test que extrae correctamente el color hex de la ruta.
     */
    public function test_extracts_hex_color_from_ruta(): void
    {
        $ruta = Ruta::create(['color' => '#FF5733 - Ruta Naranja (99999)']);

        PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.4357, -100.3571);

        $nodeKeys = array_keys($builder->nodes);
        $intKeys = array_filter($nodeKeys, 'is_int');
        $firstNode = $builder->nodes[$intKeys[0]];

        $this->assertEquals('#FF5733', $firstNode['ruta_color']);
    }

    /**
     * Test que extrae correctamente el nombre de la ruta.
     */
    public function test_extracts_route_name_from_ruta(): void
    {
        $ruta = Ruta::create(['color' => '#FF5733 - Naranja Centro (99999)']);

        PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.4357, -100.3571);

        $nodeKeys = array_keys($builder->nodes);
        $intKeys = array_filter($nodeKeys, 'is_int');
        $firstNode = $builder->nodes[$intKeys[0]];

        $this->assertEquals('Naranja Centro', $firstNode['ruta_name']);
    }
}
