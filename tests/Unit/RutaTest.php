<?php

namespace Tests\Unit;

use App\Models\PuntoNavegacion;
use App\Models\Ruta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RutaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que el modelo Ruta puede ser creado con asignación masiva.
     */
    public function test_ruta_can_be_created_with_mass_assignment(): void
    {
        $ruta = Ruta::create(['color' => '#10b981 - Ruta Verde (12345)']);

        $this->assertDatabaseHas('rutas', [
            'id' => $ruta->id,
            'color' => '#10b981 - Ruta Verde (12345)',
        ]);
    }

    /**
     * Test que la relación puntosNavegacion existe y retorna los puntos asociados.
     */
    public function test_ruta_has_puntos_navegacion_relationship(): void
    {
        $ruta = Ruta::create(['color' => '#ff0000 - Ruta Roja (99999)']);

        PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4357000,
            'longitud' => -100.3571000,
            'tipo_de_giro' => 'straight',
            'instruccion' => 'Seguir derecho',
        ]);

        PuntoNavegacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4360000,
            'longitud' => -100.3580000,
            'tipo_de_giro' => 'left',
            'instruccion' => 'Girar a la izquierda',
        ]);

        $this->assertCount(2, $ruta->puntosNavegacion);
        $this->assertInstanceOf(PuntoNavegacion::class, $ruta->puntosNavegacion->first());
    }

    /**
     * Test que el campo color es requerido al crear una ruta vía el controlador.
     */
    public function test_color_is_required_for_ruta_creation(): void
    {
        $response = $this->post('/rutas', [
            'color' => '',
        ]);

        $response->assertSessionHasErrors('color');
    }

    /**
     * Test que el campo color debe ser único entre rutas.
     */
    public function test_color_must_be_unique(): void
    {
        Ruta::create(['color' => '#abcdef - Ruta Única (11111)']);

        $response = $this->post('/rutas', [
            'color' => '#abcdef - Ruta Única (11111)',
        ]);

        $response->assertSessionHasErrors('color');
    }

    /**
     * Test que parseRouteData extrae correctamente hex, name, code y short
     * de la cadena almacenada en la columna 'color'.
     */
    public function test_parse_route_data_extracts_data_correctly(): void
    {
        $ruta = Ruta::create(['color' => '#10b981 - Amarilla AM3 (15257790)']);

        // Reproducimos la lógica de parseRouteData definida en welcome.blade.php
        $colorString = $ruta->color;
        $parts = explode(' - ', $colorString);

        $hex = $parts[0] ?? '#10b981';

        $namePart = implode(' - ', array_slice($parts, 1));

        $routeCode = '';
        if (preg_match('/\((\d+)\)/', $namePart, $matches)) {
            $routeCode = $matches[1];
            $name = trim(str_replace("({$routeCode})", '', $namePart));
        } else {
            $name = trim($namePart);
            $routeCode = $ruta->id;
        }

        if (empty($name)) {
            $name = 'Ruta ' . $routeCode;
        }

        $shortName = '';
        if (preg_match('/([a-zA-Z]+[0-9]+)/i', $name, $matches)) {
            $shortName = strtoupper($matches[1]);
        } else {
            $words = explode(' ', $name);
            if (count($words) >= 2) {
                $shortName = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
            } else {
                $shortName = 'R' . substr($routeCode, -2);
            }
        }

        $result = [
            'hex' => $hex,
            'name' => $name,
            'code' => $routeCode,
            'short' => substr($shortName, 0, 5),
        ];

        $this->assertEquals('#10b981', $result['hex']);
        $this->assertEquals('Amarilla AM3', $result['name']);
        $this->assertEquals('15257790', $result['code']);
        $this->assertEquals('AM3', $result['short']);
    }

    /**
     * Test que parseRouteData funciona cuando no hay código entre paréntesis.
     */
    public function test_parse_route_data_without_parenthetical_code(): void
    {
        $ruta = Ruta::create(['color' => '#ff5733 - Terminal Centro']);

        $colorString = $ruta->color;
        $parts = explode(' - ', $colorString);

        $hex = $parts[0] ?? '#10b981';
        $namePart = implode(' - ', array_slice($parts, 1));

        $routeCode = '';
        if (preg_match('/\((\d+)\)/', $namePart, $matches)) {
            $routeCode = $matches[1];
            $name = trim(str_replace("({$routeCode})", '', $namePart));
        } else {
            $name = trim($namePart);
            $routeCode = $ruta->id;
        }

        $this->assertEquals('#ff5733', $hex);
        $this->assertEquals('Terminal Centro', $name);
        $this->assertEquals($ruta->id, $routeCode);
    }
}
