<?php

namespace Tests\Feature;

use Tests\TestCase;

class CortexRescueTest extends TestCase
{
    /**
     * Test local access is allowed to rescue console.
     */
    public function test_local_access_is_allowed_to_rescue_console()
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('/cortex/rescue');

        // Se espera un 200 porque SystemValidatorService maneja internamente la falla de BD sin lanzar excepción
        $response->assertStatus(200);
        $response->assertViewIs('cortex.rescue');
    }

    /**
     * Test external access is denied to rescue console.
     */
    public function test_external_access_is_denied_to_rescue_console()
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
            ->get('/cortex/rescue');

        $response->assertStatus(403);
    }

    /**
     * Test access with rescue key is allowed from external IP.
     */
    public function test_access_with_rescue_key_is_allowed()
    {
        putenv('CORTEX_RESCUE_KEY=secret_rescue_token');

        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
            ->get('/cortex/rescue?key=secret_rescue_token');

        $response->assertStatus(200);
        $response->assertViewIs('cortex.rescue');
        
        putenv('CORTEX_RESCUE_KEY');
    }
}
