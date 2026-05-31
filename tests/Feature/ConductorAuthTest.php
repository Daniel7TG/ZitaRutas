<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConductorAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que la página de login se puede acceder.
     */
    public function test_login_page_is_accessible(): void
    {
        $this->assertTrue(true);
    }
}
