<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiTest extends TestCase
{
    /**
     * Test that the API test endpoint returns a successful response.
     */
    public function test_api_test_endpoint_returns_success(): void
    {
        $response = $this->get('/api/test');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => '¡OhSansi Backend API funcionando correctamente!',
                    'status' => 'success'
                ])
                ->assertJsonStructure([
                    'message',
                    'status',
                    'timestamp'
                ]);
    }
} 