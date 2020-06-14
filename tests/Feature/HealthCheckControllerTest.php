<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_responds_to_health_check_requests(): void
    {
        $this->get('/health-check')
            ->assertOk();
    }
}
