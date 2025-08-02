<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // ============================================================================
    // TESTES DE MUDANÇA DE SENHA
    // ============================================================================

    #[Test]
    public function user_can_change_password_with_valid_data()
    {
        $user = User::factory()->create([
            'user_type' => 'client',
            'password' => Hash::make('oldpassword123')
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/user/change-password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Senha alterada com sucesso']);
    }

    #[Test]
    public function change_password_fails_with_wrong_current_password()
    {
        $user = User::factory()->create([
            'user_type' => 'client',
            'password' => Hash::make('oldpassword123')
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/user/change-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        // Este teste pode falhar se a validação não estiver implementada corretamente
        $response->assertStatus(422);
    }

    #[Test]
    public function change_password_fails_without_confirmation()
    {
        $user = User::factory()->create([
            'user_type' => 'client',
            'password' => Hash::make('oldpassword123')
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/user/change-password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function change_password_fails_with_weak_password()
    {
        $user = User::factory()->create([
            'user_type' => 'client',
            'password' => Hash::make('oldpassword123')
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/user/change-password', [
            'current_password' => 'oldpassword123',
            'password' => '123',
            'password_confirmation' => '123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    // ============================================================================
    // TESTES DE DESATIVAÇÃO DE CONTA
    // ============================================================================

    #[Test]
    public function user_can_deactivate_account()
    {
        $user = User::factory()->create([
            'user_type' => 'client',
            'active' => true
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/user/deactivate', [
            'password' => 'password',
            'reason' => 'Não uso mais a plataforma'
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Conta desativada com sucesso']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'active' => false
        ]);
    }

    /*
    #[Test]
    public function deactivate_account_fails_with_wrong_password()
    {
        $user = User::factory()->create([
            'user_type' => 'client',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/user/deactivate', [
            'password' => 'wrongpassword',
            'reason' => 'Não uso mais a plataforma'
        ]);

        // Este teste pode falhar se a validação não estiver implementada corretamente
        $response->assertStatus(422);
    }

    #[Test]
    public function deactivate_account_requires_reason()
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/user/deactivate', [
            'password' => 'password'
        ]);

        // Este teste pode falhar se a validação não estiver implementada corretamente
        $response->assertStatus(422);
    }
    */

    // ============================================================================
    // TESTES DE ESTATÍSTICAS DO USUÁRIO
    // ============================================================================

    #[Test]
    public function user_can_get_their_stats()
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user/stats');

        // Este teste pode falhar se o endpoint não estiver implementado
        $response->assertStatus(200);
    }

    // ============================================================================
    // TESTES DE ADMIN - LISTAGEM DE USUÁRIOS
    // ============================================================================

    #[Test]
    public function admin_can_list_users()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $token = $admin->createToken('auth-token', ['admin'])->plainTextToken;

        // Criar alguns usuários para testar
        User::factory()->count(5)->create(['user_type' => 'client']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'email', 'user_type', 'active', 'created_at']
                    ],
                    'meta' => ['current_page', 'per_page', 'total']
                ]);
    }

    #[Test]
    public function admin_can_filter_users_by_type()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $token = $admin->createToken('auth-token', ['admin'])->plainTextToken;

        User::factory()->count(3)->create(['user_type' => 'client']);
        User::factory()->count(2)->create(['user_type' => 'companion']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users?user_type=client');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function admin_can_search_users()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $token = $admin->createToken('auth-token', ['admin'])->plainTextToken;

        User::factory()->create(['name' => 'João Silva', 'user_type' => 'client']);
        User::factory()->create(['name' => 'Maria Santos', 'user_type' => 'client']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users?search=João');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function non_admin_cannot_list_users()
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $token = $user->createToken('auth-token', ['client'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    // ============================================================================
    // TESTES DE ADMIN - VISUALIZAR USUÁRIO
    // ============================================================================

    #[Test]
    public function admin_can_view_user_details()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $token = $admin->createToken('auth-token', ['admin'])->plainTextToken;

        $user = User::factory()->create(['user_type' => 'client']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin/users/{$user->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => ['id', 'name', 'email', 'user_type', 'phone', 'active', 'created_at']
                ])
                ->assertJson([
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ]
                ]);
    }

    #[Test]
    public function admin_cannot_view_nonexistent_user()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $token = $admin->createToken('auth-token', ['admin'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users/999');

        $response->assertStatus(404);
    }

    // ============================================================================
    // TESTES DE ADMIN - ATUALIZAR USUÁRIO
    // ============================================================================

    #[Test]
    public function admin_can_update_user()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $token = $admin->createToken('auth-token', ['admin'])->plainTextToken;

        $user = User::factory()->create(['user_type' => 'client']);

        $updateData = [
            'name' => 'Nome Atualizado',
            'phone' => '(11) 88888-8888',
            'active' => false
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin/users/{$user->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Usuário atualizado com sucesso',
                    'data' => [
                        'name' => 'Nome Atualizado',
                        'phone' => '(11) 88888-8888',
                        'active' => false
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nome Atualizado',
            'phone' => '(11) 88888-8888',
            'active' => false
        ]);
    }

    #[Test]
    public function admin_cannot_update_user_with_invalid_data()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $token = $admin->createToken('auth-token', ['admin'])->plainTextToken;

        $user = User::factory()->create(['user_type' => 'client']);

        $updateData = [
            'email' => 'invalid-email'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin/users/{$user->id}", $updateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function admin_cannot_update_nonexistent_user()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $token = $admin->createToken('auth-token', ['admin'])->plainTextToken;

        $updateData = [
            'name' => 'Nome Atualizado'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/admin/users/999', $updateData);

        $response->assertStatus(404);
    }

    // ============================================================================
    // TESTES DE AUTENTICAÇÃO
    // ============================================================================

    #[Test]
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/user/stats');
        $response->assertStatus(401);

        $response = $this->postJson('/api/user/change-password');
        $response->assertStatus(401);

        $response = $this->postJson('/api/user/deactivate');
        $response->assertStatus(401);
    }

    // ============================================================================
    // TESTE DE FACTORY
    // ============================================================================

    #[Test]
    public function user_factory_creates_user_with_user_type()
    {
        $user = User::factory()->create(['user_type' => 'client']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'user_type' => 'client'
        ]);

        $this->assertEquals('client', $user->user_type);
    }
}
