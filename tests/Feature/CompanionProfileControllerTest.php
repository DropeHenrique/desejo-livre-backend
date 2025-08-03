<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CompanionProfile;
use App\Models\State;
use App\Models\City;
use App\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CompanionProfileControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Desabilitar Scout completamente para os testes
        config(['scout.driver' => 'null']);

        // Criar dados geográficos necessários
        $this->state = State::factory()->create();
        $this->city = City::factory()->create(['state_id' => $this->state->id]);
        $this->district = District::factory()->create(['city_id' => $this->city->id]);
    }

    // ============================================================================
    // TESTES PÚBLICOS - LISTAGEM DE ACOMPANHANTES
    // ============================================================================

    #[Test]
    public function can_list_companion_profiles()
    {
        CompanionProfile::factory()->count(5)->create([
            'verified' => true,
            'city_id' => $this->city->id
        ]);

        $response = $this->getJson('/api/companions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'artistic_name', 'age', 'verified', 'online_status',
                            'city' => ['id', 'name', 'state' => ['id', 'name', 'uf']]
                        ]
                    ],
                    'meta' => [
                        'current_page', 'per_page', 'total', 'last_page'
                    ],
                    'links'
                ]);
    }

    #[Test]
    public function can_filter_companions_by_city()
    {
        $city2 = City::factory()->create(['state_id' => $this->state->id]);

        CompanionProfile::factory()->create([
            'verified' => true,
            'city_id' => $this->city->id
        ]);
        CompanionProfile::factory()->create([
            'verified' => true,
            'city_id' => $city2->id
        ]);

        $response = $this->getJson("/api/companions?city_id={$this->city->id}");

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function can_filter_companions_by_age_range()
    {
        CompanionProfile::factory()->create([
            'verified' => true,
            'age' => 20,
            'city_id' => $this->city->id
        ]);
        CompanionProfile::factory()->create([
            'verified' => true,
            'age' => 30,
            'city_id' => $this->city->id
        ]);

        $response = $this->getJson('/api/companions?age_min=25&age_max=35');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function can_search_companions_by_name()
    {
        CompanionProfile::factory()->create([
            'verified' => true,
            'artistic_name' => 'Maria Silva',
            'city_id' => $this->city->id
        ]);
        CompanionProfile::factory()->create([
            'verified' => true,
            'artistic_name' => 'João Santos',
            'city_id' => $this->city->id
        ]);

        $response = $this->getJson('/api/companions?search=Maria');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function can_filter_companions_by_online_status()
    {
        CompanionProfile::factory()->create([
            'verified' => true,
            'online_status' => true,
            'city_id' => $this->city->id
        ]);
        CompanionProfile::factory()->create([
            'verified' => true,
            'online_status' => false,
            'city_id' => $this->city->id
        ]);

        $response = $this->getJson('/api/companions?online=1');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function can_sort_companions_by_age()
    {
        CompanionProfile::factory()->create([
            'verified' => true,
            'age' => 30,
            'city_id' => $this->city->id
        ]);
        CompanionProfile::factory()->create([
            'verified' => true,
            'age' => 20,
            'city_id' => $this->city->id
        ]);

        $response = $this->getJson('/api/companions?sort_by=age&sort_order=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(20, $data[0]['age']);
        $this->assertEquals(30, $data[1]['age']);
    }

    // ============================================================================
    // TESTES PÚBLICOS - VISUALIZAR PERFIL
    // ============================================================================

    #[Test]
    public function can_view_companion_profile_by_slug()
    {
        $companion = CompanionProfile::factory()->create([
            'verified' => true,
            'slug' => 'maria-silva',
            'city_id' => $this->city->id
        ]);

        $response = $this->getJson('/api/companions/maria-silva');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'artistic_name', 'age', 'verified', 'online_status',
                        'about_me', 'height', 'weight', 'eye_color', 'hair_color',
                        'city' => ['id', 'name', 'state' => ['id', 'name', 'uf']]
                    ]
                ]);
    }

    #[Test]
    public function cannot_view_unverified_companion_profile()
    {
        CompanionProfile::factory()->create([
            'verified' => false,
            'slug' => 'maria-silva',
            'city_id' => $this->city->id
        ]);

        $response = $this->getJson('/api/companions/maria-silva');

        $response->assertStatus(404);
    }

    #[Test]
    public function cannot_view_nonexistent_companion_profile()
    {
        $response = $this->getJson('/api/companions/nonexistent-slug');

        $response->assertStatus(404);
    }

    // ============================================================================
    // TESTES AUTENTICADOS - PERFIL PRÓPRIO
    // ============================================================================

    #[Test]
    public function companion_can_view_own_profile()
    {
        $companion = CompanionProfile::factory()->create([
            'verified' => true,
            'city_id' => $this->city->id
        ]);

        $token = $companion->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/companion/my-profile');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'artistic_name', 'age', 'verified', 'online_status',
                        'about_me', 'height', 'weight', 'eye_color', 'hair_color'
                    ]
                ]);
    }

    #[Test]
    public function non_companion_cannot_view_own_profile()
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $token = $user->createToken('auth-token', ['client'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/companion/my-profile');

        $response->assertStatus(403);
    }

    #[Test]
    public function companion_can_update_own_profile()
    {
        $companion = CompanionProfile::factory()->create([
            'verified' => true,
            'city_id' => $this->city->id
        ]);

        $token = $companion->user->createToken('test-token')->plainTextToken;

        $updateData = [
            'artistic_name' => 'Novo Nome Artístico',
            'about_me' => 'Nova descrição',
            'age' => 25
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/companion/my-profile', $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Profile updated successfully'
                ])
                ->assertJsonStructure([
                    'data' => [
                        'id', 'artistic_name', 'age', 'verified', 'online_status'
                    ]
                ]);

        $this->assertDatabaseHas('companion_profiles', [
            'id' => $companion->id,
            'artistic_name' => 'Novo Nome Artístico',
            'about_me' => 'Nova descrição',
            'age' => 25
        ]);
    }

    #[Test]
    public function companion_cannot_update_profile_with_invalid_data()
    {
        $user = User::factory()->create(['user_type' => 'companion']);
        $companion = CompanionProfile::factory()->create([
            'user_id' => $user->id,
            'city_id' => $this->city->id
        ]);
        $token = $user->createToken('auth-token', ['companion'])->plainTextToken;

        $updateData = [
            'age' => 15, // Idade inválida
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/companion/my-profile', $updateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['age']);
    }

    // ============================================================================
    // TESTES AUTENTICADOS - STATUS ONLINE/OFFLINE
    // ============================================================================

    #[Test]
    public function companion_can_set_online_status()
    {
        $companion = CompanionProfile::factory()->create([
            'verified' => true,
            'online_status' => false,
            'city_id' => $this->city->id
        ]);

        $token = $companion->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/companion/online');

        $response->assertStatus(200)
                ->assertJson(['message' => 'Status updated to online']);

        $this->assertDatabaseHas('companion_profiles', [
            'id' => $companion->id,
            'online_status' => true
        ]);
    }

    #[Test]
    public function companion_can_set_offline_status()
    {
        $companion = CompanionProfile::factory()->create([
            'verified' => true,
            'online_status' => true,
            'city_id' => $this->city->id
        ]);

        $token = $companion->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/companion/offline');

        $response->assertStatus(200)
                ->assertJson(['message' => 'Status updated to offline']);

        $this->assertDatabaseHas('companion_profiles', [
            'id' => $companion->id,
            'online_status' => false
        ]);
    }

    // ============================================================================
    // TESTES AUTENTICADOS - ESTATÍSTICAS
    // ============================================================================

    #[Test]
    public function companion_can_get_own_stats()
    {
        $companion = CompanionProfile::factory()->create([
            'verified' => true,
            'city_id' => $this->city->id
        ]);

        $token = $companion->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/companion/stats');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'profile_views', 'total_reviews', 'average_rating',
                    'total_favorites', 'plan_expires_at', 'has_active_plan'
                ]);
    }

    // ============================================================================
    // TESTES DE ADMIN - MODERAÇÃO
    // ============================================================================

    #[Test]
    public function admin_can_list_pending_companions()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $token = $admin->createToken('auth-token', ['admin'])->plainTextToken;

        CompanionProfile::factory()->count(3)->create([
            'verified' => false,
            'city_id' => $this->city->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/companions/pending');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function admin_can_verify_companion()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $companion = CompanionProfile::factory()->create([
            'verified' => false,
            'city_id' => $this->city->id
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/admin/companions/{$companion->id}/verify");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Profile verified successfully']);

        $this->assertDatabaseHas('companion_profiles', [
            'id' => $companion->id,
            'verified' => true
        ]);
    }

    #[Test]
    public function admin_can_reject_companion()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $companion = CompanionProfile::factory()->create([
            'verified' => true,
            'city_id' => $this->city->id
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/admin/companions/{$companion->id}/reject", [
            'reason' => 'Documentação insuficiente'
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Profile rejected']);

        $this->assertDatabaseHas('companion_profiles', [
            'id' => $companion->id,
            'verified' => false
        ]);
    }

    #[Test]
    public function non_admin_cannot_moderate_companions()
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $companion = CompanionProfile::factory()->create([
            'verified' => false,
            'city_id' => $this->city->id
        ]);
        $token = $user->createToken('auth-token', ['client'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/admin/companions/{$companion->id}/verify");

        $response->assertStatus(403);
    }

    // ============================================================================
    // TESTES DE AUTENTICAÇÃO
    // ============================================================================

    #[Test]
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/companion/my-profile');
        $response->assertStatus(401);

        $response = $this->putJson('/api/companion/my-profile');
        $response->assertStatus(401);

        $response = $this->postJson('/api/companion/online');
        $response->assertStatus(401);

        $response = $this->getJson('/api/companion/stats');
        $response->assertStatus(401);
    }
}
