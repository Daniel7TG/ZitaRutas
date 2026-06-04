<?php

namespace Tests\Feature;

use App\Models\PuntoNavegacion;
use App\Models\Ruta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que el endpoint retorna error 422 sin campos requeridos.
     */
    public function test_shortest_path_returns_422_without_required_fields(): void
    {
        $response = $this->postJson('/api/routing/shortest-path', []);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Error de validacion.',
        ]);
    }

    /**
     * Test que el endpoint retorna error 422 con latitud fuera de rango.
     */
    public function test_shortest_path_returns_422_with_invalid_latitude(): void
    {
        $response = $this->postJson('/api/routing/shortest-path', [
            'origen' => ['latitud' => 100, 'longitud' => -100.3571],
            'destino' => ['latitud' => 19.4360, 'longitud' => -100.3580],
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test que el endpoint retorna error 422 con longitud fuera de rango.
     */
    public function test_shortest_path_returns_422_with_invalid_longitude(): void
    {
        $response = $this->postJson('/api/routing/shortest-path', [
            'origen' => ['latitud' => 19.4357, 'longitud' => -200],
            'destino' => ['latitud' => 19.4360, 'longitud' => -100.3580],
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test que el endpoint retorna respuesta exitosa con datos válidos.
     */
    public function test_shortest_path_returns_success_with_valid_data(): void
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

        $response = $this->postJson('/api/routing/shortest-path', [
            'origen' => ['latitud' => 19.4357, 'longitud' => -100.3571],
            'destino' => ['latitud' => 19.4360, 'longitud' => -100.3580],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Test que el endpoint retorna data null cuando origin y dest están lejos de todos los puntos.
     */
    public function test_shortest_path_returns_null_when_points_are_far(): void
    {
        $ruta = Ruta::create(['color' => '#FF0000 - Ruta Roja (12345)']);

        PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
        ]);

        // Origin y dest a ~16km de los puntos (fuera de rango caminable)
        $response = $this->postJson('/api/routing/shortest-path', [
            'origen' => ['latitud' => 19.5000, 'longitud' => -100.5000],
            'destino' => ['latitud' => 19.6000, 'longitud' => -100.6000],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => null,
        ]);
    }

    /**
     * Test que el endpoint acepta opciones de max_trasbordos.
     */
    public function test_shortest_path_accepts_max_trasbordos_option(): void
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

        $response = $this->postJson('/api/routing/shortest-path', [
            'origen' => ['latitud' => 19.4357, 'longitud' => -100.3571],
            'destino' => ['latitud' => 19.4360, 'longitud' => -100.3580],
            'opciones' => ['max_trasbordos' => 2],
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test que el endpoint retorna error 422 con max_trasbordos fuera de rango.
     */
    public function test_shortest_path_returns_422_with_invalid_max_trasbordos(): void
    {
        $response = $this->postJson('/api/routing/shortest-path', [
            'origen' => ['latitud' => 19.4357, 'longitud' => -100.3571],
            'destino' => ['latitud' => 19.4360, 'longitud' => -100.3580],
            'opciones' => ['max_trasbordos' => 10],
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test que la respuesta contiene segmentos cuando hay ruta.
     */
    public function test_shortest_path_response_contains_segments(): void
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

        $response = $this->postJson('/api/routing/shortest-path', [
            'origen' => ['latitud' => 19.4357, 'longitud' => -100.3571],
            'destino' => ['latitud' => 19.4360, 'longitud' => -100.3580],
        ]);

        $response->assertStatus(200);

        $data = $response->json('data');
        if ($data !== null) {
            $this->assertArrayHasKey('segmentos', $data);
            $this->assertIsArray($data['segmentos']);
        }
    }

    /**
     * Test que el endpoint requiere origen y destino.
     */
    public function test_shortest_path_requires_origen_and_destino(): void
    {
        $response = $this->postJson('/api/routing/shortest-path', [
            'origen' => ['latitud' => 19.4357, 'longitud' => -100.3571],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('destino');
    }

    /**
     * Test que el endpoint requiere latitud y longitud en origen.
     */
    public function test_shortest_path_requires_latitud_and_longitud_in_origen(): void
    {
        $response = $this->postJson('/api/routing/shortest-path', [
            'origen' => ['latitud' => 19.4357],
            'destino' => ['latitud' => 19.4360, 'longitud' => -100.3580],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('origen.longitud');
    }
}
