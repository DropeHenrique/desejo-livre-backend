<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function can_register_client()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => $this->faker->phoneNumber,
        ];

        $response = $this->postJson('/api/auth/register/client', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'user' => ['id', 'name', 'email', 'user_type'],
                    'token'
                ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'user_type' => 'client'
        ]);
    }

    #[Test]
    public function can_register_companion()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => $this->faker->phoneNumber,
        ];

        $response = $this->postJson('/api/auth/register/companion', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'user' => ['id', 'name', 'email', 'user_type'],
                    'token'
                ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'user_type' => 'companion'
        ]);
    }

    #[Test]
    public function can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'client'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => ['id', 'name', 'email', 'user_type'],
                    'token'
                ]);
    }

    #[Test]
    public function cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                ->assertJson(['message' => 'Credenciais inválidas']);
    }

    #[Test]
    public function client_can_access_protected_routes()
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $token = $user->createToken('auth-token', ['client'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/client/profile');

        $response->assertStatus(200);
    }

    #[Test]
    public function companion_can_access_protected_routes()
    {
        $user = User::factory()->create(['user_type' => 'companion']);

        // Criar dados necessários se não existirem
        $state = \App\Models\State::first();
        if (!$state) {
            $state = \App\Models\State::factory()->create();
        }

        $city = \App\Models\City::first();
        if (!$city) {
            $city = \App\Models\City::factory()->create(['state_id' => $state->id]);
        }

        $plan = \App\Models\Plan::where('user_type', 'companion')->first();
        if (!$plan) {
            $plan = \App\Models\Plan::factory()->create(['user_type' => 'companion']);
        }

        \App\Models\CompanionProfile::factory()->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
            'plan_id' => $plan->id,
        ]);

        $token = $user->createToken('auth-token', ['companion'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/companion/profile');

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_access_protected_routes()
    {
        $user = User::factory()->create(['user_type' => 'admin']);
        $token = $user->createToken('auth-token', ['admin'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
    }

    #[Test]
    public function user_cannot_access_routes_without_proper_permissions()
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $token = $user->createToken('auth-token', ['client'])->plainTextToken;

        // Cliente tentando acessar rota de acompanhante
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/companion/my-profile');

        $response->assertStatus(403);
    }

    #[Test]
    public function can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
                ->assertJson(['message' => 'Logged out successfully']);
    }

    #[Test]
    public function registration_requires_valid_email()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register/client', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function registration_requires_password_confirmation()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ];

        $response = $this->postJson('/api/auth/register/client', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }
}
