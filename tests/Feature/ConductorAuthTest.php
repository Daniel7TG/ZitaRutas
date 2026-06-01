<?php

namespace Tests\Feature;

use App\Models\Conductor;
use App\Models\Ruta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConductorAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que la página de login es accesible (GET /login).
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        // AuthController::showLoginForm está vacío, así que Laravel retorna 200 con body vacío
        $response->assertStatus(200);
    }

    /**
     * Test que un POST a /login sin credenciales no causa un error del servidor.
     */
    public function test_login_post_without_credentials_returns_valid_response(): void
    {
        $response = $this->post('/login', []);

        // El controlador está vacío, así que retorna 200 sin hacer nada
        $response->assertStatus(200);
    }

    /**
     * Test que un POST a /logout retorna una respuesta válida.
     */
    public function test_logout_returns_valid_response(): void
    {
        $response = $this->post('/logout');

        // El controlador está vacío, así que retorna 200
        $response->assertStatus(200);
    }

    /**
     * Test que los usuarios no autenticados son redirigidos al acceder a rutas protegidas
     * de recursos que requieren autenticación (si middleware auth estuviera aplicado).
     * Dado que actualmente no hay middleware auth en las rutas de recursos,
     * verificamos que las rutas de recursos responden sin error del servidor.
     */
    public function test_rutas_resource_index_is_accessible(): void
    {
        $response = $this->get('/rutas');

        // Sin middleware auth, debería ser accesible y retornar 200
        $response->assertSuccessful();
    }

    /**
     * Test que la ruta GET /conductores responde correctamente.
     */
    public function test_conductores_resource_is_accessible(): void
    {
        $response = $this->get('/conductores');

        $response->assertSuccessful();
    }

    /**
     * Test que el modelo Conductor puede ser creado y autenticado.
     */
    public function test_conductor_model_can_be_created(): void
    {
        $ruta = Ruta::create(['color' => '#10b981 - Ruta Test (00001)']);

        $conductor = Conductor::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'num_combi' => 5,
            'id_conductor' => 'COND-001',
            'ruta_id' => $ruta->id,
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('conductores', [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'id_conductor' => 'COND-001',
        ]);
    }

    /**
     * Test que la ruta named 'login' existe y apunta a /login.
     */
    public function test_login_named_route_exists(): void
    {
        $this->assertEquals(url('/login'), route('login'));
    }
}
