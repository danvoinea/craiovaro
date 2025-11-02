<?php

namespace Tests\Feature;

use Tests\TestCase;

class SystemStatusTest extends TestCase
{
    /** @test */
    public function it_returns_system_status()
    {
        $response = $this->getJson('/api/status');

        $response
            ->assertOk()
            ->assertJson([
                'ok' => true,
            ])
            ->assertJsonStructure([
                'ok',
                'data' => [
                    'name',
                    'env',
                    'php_version',
                    'laravel_version',
                ],
            ]);
    }
}

