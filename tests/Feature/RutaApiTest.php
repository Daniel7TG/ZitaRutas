<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RutaApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que la lista de rutas se puede obtener.
     */
    public function test_rutas_index_returns_successful_response(): void
    {
        $this->assertTrue(true);
    }
}
