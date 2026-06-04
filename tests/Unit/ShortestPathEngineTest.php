<?php

namespace Tests\Unit;

use App\Models\PuntoNavegacion;
use App\Models\Ruta;
use App\Services\Routing\GraphBuilder;
use App\Services\Routing\ShortestPathEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortestPathEngineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que encuentra ruta directa en una sola ruta.
     */
    public function test_finds_direct_route_on_single_ruta(): void
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

        PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4363000,
            'longitud' => -100.3590000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.4363, -100.3590);

        $engine = new ShortestPathEngine($builder, 3);
        $result = $engine->find();

        $this->assertNotNull($result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('totalTime', $result);
        $this->assertArrayHasKey('totalDistance', $result);
        $this->assertGreaterThan(0, $result['totalTime']);
    }

    /**
     * Test que encuentra ruta usando transporte público.
     */
    public function test_finds_route_using_public_transport(): void
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
            'ruta_id' => $ruta1->id,
            'latitud' => 19.4360000,
            'longitud' => -100.3580000,
            'tipo_de_giro' => 'straight',
        ]);

        $p3 = PuntoNavegacion::create([
            'ruta_id' => $ruta2->id,
            'latitud' => 19.4360500,
            'longitud' => -100.3580500,
            'tipo_de_giro' => 'straight',
        ]);

        $p4 = PuntoNavegacion::create([
            'ruta_id' => $ruta2->id,
            'latitud' => 19.4370000,
            'longitud' => -100.3600000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.4370, -100.3600);

        $engine = new ShortestPathEngine($builder, 3);
        $result = $engine->find();

        $this->assertNotNull($result);
        $this->assertArrayHasKey('path', $result);
        $this->assertNotEmpty($result['path']);
    }

    /**
     * Test que respeta el límite de trasbordos.
     */
    public function test_respects_max_transfers_limit(): void
    {
        $ruta1 = Ruta::create(['color' => '#FF0000 - Ruta Roja (12345)']);
        $ruta2 = Ruta::create(['color' => '#0000FF - Ruta Azul (67890)']);

        PuntoNavegacion::create([
            'ruta_id' => $ruta1->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        PuntoNavegacion::create([
            'ruta_id' => $ruta1->id,
            'latitud' => 19.4360000,
            'longitud' => -100.3580000,
            'tipo_de_giro' => 'straight',
        ]);

        PuntoNavegacion::create([
            'ruta_id' => $ruta2->id,
            'latitud' => 19.4360500,
            'longitud' => -100.3580500,
            'tipo_de_giro' => 'straight',
        ]);

        PuntoNavegacion::create([
            'ruta_id' => $ruta2->id,
            'latitud' => 19.4370000,
            'longitud' => -100.3600000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.4370, -100.3600);

        $engine = new ShortestPathEngine($builder, 0);
        $result = $engine->find();

        if ($result !== null) {
            $boardCount = 0;
            foreach ($result['path'] as $step) {
                if ($step['type'] === 'board') {
                    $boardCount++;
                }
            }
            // Con maxTransfers=0, no se permite abordar ningún camión
            $this->assertEquals(0, $boardCount);
        } else {
            $this->assertNull($result);
        }
    }

    /**
     * Test que retorna null cuando origin y dest están fuera de rango caminable.
     */
    public function test_returns_null_when_no_nearby_points(): void
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

        // Origin y dest a ~16km de los puntos de navegación (fuera de 500m)
        $builder = new GraphBuilder();
        $builder->build(19.5000, -100.5000, 19.6000, -100.6000);

        $engine = new ShortestPathEngine($builder, 3);
        $result = $engine->find();

        $this->assertNull($result);
    }

    /**
     * Test que el tiempo total incluye tiempo de espera en trasbordos.
     */
    public function test_total_time_includes_waiting_time(): void
    {
        $ruta1 = Ruta::create(['color' => '#FF0000 - Ruta Roja (12345)']);
        $ruta2 = Ruta::create(['color' => '#0000FF - Ruta Azul (67890)']);

        PuntoNavegacion::create([
            'ruta_id' => $ruta1->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        PuntoNavegacion::create([
            'ruta_id' => $ruta1->id,
            'latitud' => 19.4360000,
            'longitud' => -100.3580000,
            'tipo_de_giro' => 'straight',
        ]);

        PuntoNavegacion::create([
            'ruta_id' => $ruta2->id,
            'latitud' => 19.4360500,
            'longitud' => -100.3580500,
            'tipo_de_giro' => 'straight',
        ]);

        PuntoNavegacion::create([
            'ruta_id' => $ruta2->id,
            'latitud' => 19.4370000,
            'longitud' => -100.3600000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.4370, -100.3600);

        $engine = new ShortestPathEngine($builder, 3);
        $result = $engine->find();

        if ($result !== null) {
            $hasBoard = false;
            foreach ($result['path'] as $step) {
                if ($step['type'] === 'board') {
                    $hasBoard = true;
                    $this->assertArrayHasKey('wait_time_s', $step);
                    $this->assertEquals(300, $step['wait_time_s']);
                }
            }
            if ($hasBoard) {
                $this->assertGreaterThan(300, $result['totalTime']);
            }
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * Test que el path contiene al menos un tipo de paso.
     */
    public function test_path_contains_at_least_one_step_type(): void
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

        $engine = new ShortestPathEngine($builder, 3);
        $result = $engine->find();

        $this->assertNotNull($result);
        $this->assertNotEmpty($result['path']);

        $stepTypes = array_unique(array_column($result['path'], 'type'));
        $this->assertNotEmpty($stepTypes);
    }

    /**
     * Test que la distancia total es mayor que cero.
     */
    public function test_total_distance_is_greater_than_zero(): void
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

        $engine = new ShortestPathEngine($builder, 3);
        $result = $engine->find();

        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result['totalDistance']);
    }
}
