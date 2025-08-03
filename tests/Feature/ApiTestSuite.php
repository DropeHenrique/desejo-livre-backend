<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\State;
use App\Models\City;
use App\Models\Plan;
use App\Models\ServiceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ApiTestSuite extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar dados básicos necessários
        $this->state = State::factory()->create();
        $this->city = City::factory()->create(['state_id' => $this->state->id]);
    }

    // ============================================================================
    // TESTE RÁPIDO DE TODOS OS ENDPOINTS PÚBLICOS
    // ============================================================================

    #[Test]
    public function all_public_endpoints_are_accessible()
    {
        // Teste de ping
        $response = $this->getJson('/api/ping');
        $response->assertStatus(200);

        // Teste de geografia
        $response = $this->getJson('/api/geography/states');
        $response->assertStatus(200);

        $response = $this->getJson('/api/geography/cities');
        $response->assertStatus(200);

        $response = $this->getJson('/api/geography/districts');
        $response->assertStatus(200);

        // Teste de tipos de serviço
        $response = $this->getJson('/api/service-types');
        $response->assertStatus(200);

        // Teste de planos
        $response = $this->getJson('/api/plans');
        $response->assertStatus(200);

        // Teste de acompanhantes
        $response = $this->getJson('/api/companions');
        $response->assertStatus(200);

        // Teste de blog
        $response = $this->getJson('/api/blog/posts');
        $response->assertStatus(200);

        $response = $this->getJson('/api/blog/categories');
        $response->assertStatus(200);
    }

    // ============================================================================
    // TESTE DE REGISTRO E LOGIN
    // ============================================================================

    #[Test]
    public function can_register_and_login_user()
    {
        // Registrar cliente
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => $this->faker->phoneNumber,
        ];

        $response = $this->postJson('/api/auth/register/client', $userData);
        $response->assertStatus(201);

        // Fazer login
        $loginData = [
            'email' => $userData['email'],
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);
        $response->assertStatus(200);

        $token = $response->json('token');

        // Testar acesso a rota protegida
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user/profile');

        $response->assertStatus(200);
    }

    // ============================================================================
    // TESTE DE ENDPOINTS AUTENTICADOS
    // ============================================================================

    #[Test]
    public function authenticated_endpoints_work_correctly()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        // Testar perfil do usuário
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user/profile');
        $response->assertStatus(200);

        // Testar estatísticas do usuário
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user/stats');
        $response->assertStatus(200);

        // Testar logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/user/logout');
        $response->assertStatus(200);
    }

    // ============================================================================
    // TESTE DE ENDPOINTS DE ADMIN
    // ============================================================================

    #[Test]
    public function admin_endpoints_work_correctly()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $token = $admin->createToken('auth-token', ['admin'])->plainTextToken;

        // Testar dashboard
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/dashboard');
        $response->assertStatus(200);

        // Testar listagem de usuários
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users');
        $response->assertStatus(200);

        // Testar criação de tipo de serviço
        $serviceTypeData = [
            'name' => 'Teste Serviço',
            'description' => 'Descrição do serviço',
            'icon' => 'icon-test',
            'active' => true
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/service-types', $serviceTypeData);
        $response->assertStatus(201);

        // Testar criação de plano
        $planData = [
            'name' => 'Plano Teste',
            'description' => 'Descrição do plano',
            'price' => 99.90,
            'duration_days' => 30,
            'features' => ['feature1', 'feature2'],
            'user_type' => 'client',
            'active' => true
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/plans', $planData);
        $response->assertStatus(201);
    }

    // ============================================================================
    // TESTE DE ENDPOINTS DE ACOMPANHANTE
    // ============================================================================

    #[Test]
    public function companion_endpoints_work_correctly()
    {
        $companion = User::factory()->create(['user_type' => 'companion']);
        $token = $companion->createToken('auth-token', ['companion'])->plainTextToken;

        // Testar perfil da acompanhante
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/companion/my-profile');
        $response->assertStatus(200);

        // Testar estatísticas da acompanhante
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/companion/stats');
        $response->assertStatus(200);

        // Testar status online/offline
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/companion/online');
        $response->assertStatus(200);
    }

    // ============================================================================
    // TESTE DE ENDPOINTS DE ASSINATURA
    // ============================================================================

    #[Test]
    public function subscription_endpoints_work_correctly()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        // Testar listagem de assinaturas
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/subscriptions');
        $response->assertStatus(200);

        // Testar criação de assinatura
        $subscriptionData = [
            'plan_id' => $plan->id,
            'payment_method' => 'credit_card',
            'auto_renew' => true
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/subscriptions', $subscriptionData);
        $response->assertStatus(201);
    }

    // ============================================================================
    // TESTE DE ENDPOINTS DE FAVORITOS
    // ============================================================================

    #[Test]
    public function favorite_endpoints_work_correctly()
    {
        $user = User::factory()->create();
        $companion = \App\Models\CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        // Testar listagem de favoritos
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites');
        $response->assertStatus(200);

        // Testar adição de favorito
        $favoriteData = [
            'companion_profile_id' => $companion->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites', $favoriteData);
        $response->assertStatus(201);

        // Testar toggle de favorito
        $toggleData = [
            'companion_profile_id' => $companion->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites/toggle', $toggleData);
        $response->assertStatus(200);
    }

    // ============================================================================
    // TESTE DE ENDPOINTS DE AVALIAÇÕES
    // ============================================================================

    #[Test]
    public function review_endpoints_work_correctly()
    {
        $user = User::factory()->create();
        $companion = \App\Models\CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        // Testar listagem de avaliações
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/reviews');
        $response->assertStatus(200);

        // Testar criação de avaliação
        $reviewData = [
            'companion_profile_id' => $companion->id,
            'rating' => 5,
            'comment' => 'Excelente atendimento!'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/reviews', $reviewData);
        $response->assertStatus(201);
    }

    // ============================================================================
    // TESTE DE ENDPOINTS DE CEP
    // ============================================================================

    #[Test]
    public function cep_endpoints_work_correctly()
    {
        // Testar validação de CEP
        $response = $this->postJson('/api/cep/validate', [
            'cep' => '01001-000'
        ]);
        $response->assertStatus(200);

        // Testar busca por CEP (mock)
        \Illuminate\Support\Facades\Http::fake([
            'viacep.com.br/ws/01001000/json/' => \Illuminate\Support\Facades\Http::response([
                'cep' => '01001-000',
                'logradouro' => 'Praça da Sé',
                'bairro' => 'Sé',
                'localidade' => 'São Paulo',
                'uf' => 'SP'
            ], 200)
        ]);

        $response = $this->postJson('/api/cep/search', [
            'cep' => '01001000'
        ]);
        $response->assertStatus(200);
    }

    // ============================================================================
    // TESTE DE PERFORMANCE GERAL
    // ============================================================================

    #[Test]
    public function api_performs_well_under_load()
    {
        // Criar dados de teste
        User::factory()->count(10)->create();
        State::factory()->count(5)->create();
        City::factory()->count(20)->create();
        Plan::factory()->count(5)->create();
        ServiceType::factory()->count(5)->create();

        $startTime = microtime(true);

        // Fazer várias requisições simultâneas
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->getJson('/api/geography/states');
            $responses[] = $this->getJson('/api/service-types');
            $responses[] = $this->getJson('/api/plans');
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Verificar que todas as respostas foram bem-sucedidas
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // Verificar que o tempo total foi razoável
        $this->assertLessThan(5.0, $executionTime, 'API should handle multiple requests within 5 seconds');
    }

    // ============================================================================
    // TESTE DE AUTENTICAÇÃO E AUTORIZAÇÃO
    // ============================================================================

    #[Test]
    public function authentication_and_authorization_work_correctly()
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $companion = User::factory()->create(['user_type' => 'companion']);
        $admin = User::factory()->create(['user_type' => 'admin']);

        $clientToken = $client->createToken('auth-token', ['client'])->plainTextToken;
        $companionToken = $companion->createToken('auth-token', ['companion'])->plainTextToken;
        $adminToken = $admin->createToken('auth-token', ['admin'])->plainTextToken;

        // Cliente não pode acessar rotas de acompanhante
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $clientToken,
        ])->getJson('/api/companion/my-profile');
        $response->assertStatus(403);

        // Cliente não pode acessar rotas de admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $clientToken,
        ])->getJson('/api/admin/dashboard');
        $response->assertStatus(403);

        // Acompanhante não pode acessar rotas de admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $companionToken,
        ])->getJson('/api/admin/dashboard');
        $response->assertStatus(403);

        // Admin pode acessar todas as rotas
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
        ])->getJson('/api/admin/dashboard');
        $response->assertStatus(200);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
        ])->getJson('/api/user/profile');
        $response->assertStatus(200);
    }

    // ============================================================================
    // TESTE DE VALIDAÇÃO DE DADOS
    // ============================================================================

    #[Test]
    public function data_validation_works_correctly()
    {
        // Testar registro com dados inválidos
        $invalidUserData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456'
        ];

        $response = $this->postJson('/api/auth/register/client', $invalidUserData);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);

        // Testar login com dados inválidos
        $invalidLoginData = [
            'email' => 'invalid-email',
            'password' => ''
        ];

        $response = $this->postJson('/api/auth/login', $invalidLoginData);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);
    }

    // ============================================================================
    // TESTE DE TRATAMENTO DE ERROS
    // ============================================================================

    #[Test]
    public function error_handling_works_correctly()
    {
        // Testar endpoint inexistente
        $response = $this->getJson('/api/nonexistent-endpoint');
        $response->assertStatus(404);

        // Testar método não permitido
        $response = $this->postJson('/api/geography/states');
        $response->assertStatus(405);

        // Testar acesso sem autenticação
        $response = $this->getJson('/api/user/profile');
        $response->assertStatus(401);

        // Testar token inválido
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/user/profile');
        $response->assertStatus(401);
    }
}
