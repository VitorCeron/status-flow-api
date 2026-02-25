<?php

namespace Tests\Traits;

use Illuminate\Http\Response;

trait AuthenticationTrait 
{
    /**
     * Método auxiliar para autenticação
     * 
     * @param array $credentials
     * @return string|null
     */
    private function authenticate(array $credentials = []): string|null
    {
        if (empty($credentials)) {
            return null;
        }

        $response = $this->postJson('/api/auth/login', $credentials);
        
        $response->assertStatus(Response::HTTP_OK);
        
        return $response->json('data.access_token');
    }
}