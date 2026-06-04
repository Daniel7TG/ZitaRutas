<?php

namespace Tests\Unit;

use App\Models\PuntoNavegacion;
use App\Models\Ruta;
use App\Services\Routing\GraphBuilder;
use App\Services\Routing\RouteFormatter;
use App\Services\Routing\ShortestPathEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteFormatterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que format retorna estructura correcta cuando no hay ruta.
     */
    public function test_format_returns_correct_structure_when_no_route(): void
    {
        $ruta = Ruta::create(['color' => '#FF0000 - Ruta Roja (12345)']);

        PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        $builder = new GraphBuilder();
        $builder->build(19.4357, -100.3571, 19.4357, -100.3571);

        $formatter = new RouteFormatter($builder);
        $response = $formatter->format(null, 19.4357, -100.3571, 19.4357, -100.3571);

        $this->assertTrue($response['success']);
        $this->assertNull($response['data']);
        $this->assertArrayHasKey('message', $response);
    }

    /**
     * Test que format retorna segmentos con estructura correcta.
     */
    public function test_format_returns_segments_with_correct_structure(): void
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

        $formatter = new RouteFormatter($builder);
        $response = $formatter->format($result, 19.4357, -100.3571, 19.4360, -100.3580);

        $this->assertTrue($response['success']);
        $this->assertNotNull($response['data']);
        $this->assertArrayHasKey('tiempo_total_estimado_s', $response['data']);
        $this->assertArrayHasKey('distancia_total_m', $response['data']);
        $this->assertArrayHasKey('numero_trasbordos', $response['data']);
        $this->assertArrayHasKey('segmentos', $response['data']);
    }

    /**
     * Test que los segmentos de caminata tienen la estructura correcta.
     */
    public function test_walking_segments_have_correct_structure(): void
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

        $formatter = new RouteFormatter($builder);
        $response = $formatter->format($result, 19.4357, -100.3571, 19.4360, -100.3580);

        $walkingSegments = array_filter($response['data']['segmentos'], fn($s) => $s['tipo'] === 'caminata');

        foreach ($walkingSegments as $seg) {
            $this->assertArrayHasKey('descripcion', $seg);
            $this->assertArrayHasKey('distancia_m', $seg);
            $this->assertArrayHasKey('tiempo_s', $seg);
            $this->assertArrayHasKey('desde', $seg);
            $this->assertArrayHasKey('hasta', $seg);
            $this->assertArrayHasKey('latitud', $seg['desde']);
            $this->assertArrayHasKey('longitud', $seg['desde']);
        }
    }

    /**
     * Test que los segmentos de transporte tienen la estructura correcta.
     */
    public function test_transport_segments_have_correct_structure(): void
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

        $formatter = new RouteFormatter($builder);
        $response = $formatter->format($result, 19.4357, -100.3571, 19.4360, -100.3580);

        $transportSegments = array_filter($response['data']['segmentos'], fn($s) => $s['tipo'] === 'transporte');

        foreach ($transportSegments as $seg) {
            $this->assertArrayHasKey('ruta_id', $seg);
            $this->assertArrayHasKey('ruta_color', $seg);
            $this->assertArrayHasKey('ruta_nombre', $seg);
            $this->assertArrayHasKey('descripcion', $seg);
            $this->assertArrayHasKey('paradas_intermedias', $seg);
            $this->assertArrayHasKey('distancia_m', $seg);
            $this->assertArrayHasKey('tiempo_s', $seg);
            $this->assertArrayHasKey('coordenadas_ruta', $seg);
        }
    }

    /**
     * Test que el tiempo total es la suma de tiempos de segmentos.
     */
    public function test_total_time_matches_sum_of_segments(): void
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

        $formatter = new RouteFormatter($builder);
        $response = $formatter->format($result, 19.4357, -100.3571, 19.4360, -100.3580);

        $segmentTimeSum = array_sum(array_column($response['data']['segmentos'], 'tiempo_s'));
        $this->assertEquals($response['data']['tiempo_total_estimado_s'], $segmentTimeSum);
    }

    /**
     * Test que numero_trasbordos es correcto.
     */
    public function test_numero_trasbordos_is_correct(): void
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

        $formatter = new RouteFormatter($builder);
        $response = $formatter->format($result, 19.4357, -100.3571, 19.4360, -100.3580);

        // Con una sola ruta, no hay trasbordos
        $this->assertEquals(0, $response['data']['numero_trasbordos']);
    }

    /**
     * Test que distancia_total_m es entero.
     */
    public function test_distancia_total_is_integer(): void
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

        $formatter = new RouteFormatter($builder);
        $response = $formatter->format($result, 19.4357, -100.3571, 19.4360, -100.3580);

        $this->assertIsInt($response['data']['distancia_total_m']);
    }
}
