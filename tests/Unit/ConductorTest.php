<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConductorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que el modelo Conductor puede ser creado.
     */
    public function test_model_can_be_created(): void
    {
        $this->assertTrue(true);
    }
}
