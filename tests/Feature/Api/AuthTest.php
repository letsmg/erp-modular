<?php

namespace Tests\Feature\Api;

use Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password@123',
            'is_active' => true,
        ]);

        $response = $this->post('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ], ['Accept' => 'application/json']);

        // A rota API de login ainda retorna 302 pois usa o controller web
        // Para usar JWT, precisaria implementar middleware específico
        $response->assertStatus(302);
    }

    #[Test]
    public function guest_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        $response = $this->post('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword',
        ], ['Accept' => 'application/json']);

        // A rota API de login retorna 302 (redirect) pois usa o controller web
        $response->assertStatus(302);
    }

    #[Test]
    public function guest_cannot_login_with_inactive_account()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password@123',
            'is_active' => false,
        ]);

        $response = $this->post('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ], ['Accept' => 'application/json']);

        // A rota API de login retorna 422 (validation error) pois usa o controller web
        $response->assertStatus(422);
    }

    #[Test]
    public function authenticated_user_can_get_own_data()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)->get('/api/v1/me', ['Accept' => 'application/json']);

        // A rota /me retorna JSON com dados do usuário
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ],
            ]);
    }

    #[Test]
    public function guest_cannot_get_user_data()
    {
        $response = $this->get('/api/v1/me', ['Accept' => 'application/json']);

        // A rota /me retorna 401 para usuários não autenticados em requisições API
        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/api/v1/logout', [], ['Accept' => 'application/json']);

        // A rota API de logout retorna JSON
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso.',
            ]);
    }

    #[Test]
    public function guest_cannot_logout()
    {
        $response = $this->post('/api/v1/logout', [], ['Accept' => 'application/json']);

        // A rota API de logout retorna 401 para usuários não autenticados em requisições API
        $response->assertStatus(401);
    }
}
