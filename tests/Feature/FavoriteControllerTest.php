<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CompanionProfile;
use App\Models\Favorite;
use App\Models\State;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FavoriteControllerTest extends TestCase
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
    }

    protected function createUser($type = 'client')
    {
        return \App\Models\User::factory()->state(['user_type' => $type])->create();
    }

    protected function createCity($name = null, $state = null)
    {
        $name = $name ?? fake()->city;
        $state = $state ?? $this->state;
        return \App\Models\City::factory()->state([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'state_id' => $state->id,
        ])->create();
    }

    // ============================================================================
    // TESTES AUTENTICADOS - LISTAGEM DE FAVORITOS
    // ============================================================================

    #[Test]
    public function user_can_list_own_favorites()
    {
        $user = $this->createUser();
        $companion1 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion2 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion3 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion1->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion2->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion3->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'user_id', 'companion_profile_id', 'created_at'
                        ]
                    ]
                ])
                ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function user_cannot_see_other_user_favorites()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $companion = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user1->createToken('auth-token')->plainTextToken;

        Favorite::factory()->create([
            'user_id' => $user2->id,
            'companion_profile_id' => $companion->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites');

        $response->assertStatus(200)
                ->assertJsonCount(0, 'data');
    }

    // ============================================================================
    // TESTES AUTENTICADOS - ADICIONAR FAVORITO
    // ============================================================================

    #[Test]
    public function user_can_add_favorite()
    {
        $user = $this->createUser();
        $companion = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $favoriteData = [
            'companion_profile_id' => $companion->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites', $favoriteData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id', 'user_id', 'companion_profile_id', 'created_at'
                    ]
                ])
                ->assertJson([
                    'message' => 'Adicionado aos favoritos com sucesso'
                ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'companion_profile_id' => $companion->id
        ]);
    }

    #[Test]
    public function user_cannot_add_favorite_for_nonexistent_companion()
    {
        $user = $this->createUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $favoriteData = [
            'companion_profile_id' => 999
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites', $favoriteData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['companion_profile_id']);
    }

    #[Test]
    public function user_cannot_add_duplicate_favorite()
    {
        $user = $this->createUser();
        $companion = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        // Adicionar primeiro favorito
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion->id
        ]);

        // Tentar adicionar novamente
        $favoriteData = [
            'companion_profile_id' => $companion->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites', $favoriteData);

        $response->assertStatus(422);
    }

    // ============================================================================
    // TESTES AUTENTICADOS - REMOVER FAVORITO
    // ============================================================================

    #[Test]
    public function user_can_remove_favorite()
    {
        $user = $this->createUser();
        $companion = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion->id
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/favorites/{$favorite->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Removido dos favoritos com sucesso']);

        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    #[Test]
    public function user_cannot_remove_other_user_favorite()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $companion = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $favorite = Favorite::factory()->create([
            'user_id' => $user2->id,
            'companion_profile_id' => $companion->id
        ]);
        $token = $user1->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/favorites/{$favorite->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function cannot_remove_nonexistent_favorite()
    {
        $user = $this->createUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/favorites/999');

        $response->assertStatus(404);
    }

    // ============================================================================
    // TESTES AUTENTICADOS - TOGGLE FAVORITO
    // ============================================================================

    #[Test]
    public function user_can_toggle_favorite_add()
    {
        $user = $this->createUser();
        $companion = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $toggleData = [
            'companion_profile_id' => $companion->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites/toggle', $toggleData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Adicionado aos favoritos',
                    'data' => [
                        'is_favorite' => true
                    ]
                ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'companion_profile_id' => $companion->id
        ]);
    }

    #[Test]
    public function user_can_toggle_favorite_remove()
    {
        $user = $this->createUser();
        $companion = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion->id
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $toggleData = [
            'companion_profile_id' => $companion->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites/toggle', $toggleData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Removido dos favoritos',
                    'data' => [
                        'is_favorite' => false
                    ]
                ]);

        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    #[Test]
    public function toggle_favorite_requires_companion_id()
    {
        $user = $this->createUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites/toggle');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['companion_profile_id']);
    }

    // ============================================================================
    // TESTES AUTENTICADOS - LIMPAR FAVORITOS
    // ============================================================================

    #[Test]
    public function user_can_clear_all_favorites()
    {
        $user = $this->createUser();
        $companion1 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion2 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion3 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion4 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion5 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion1->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion2->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion3->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion4->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion5->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites/clear');

        $response->assertStatus(200)
                ->assertJson(['message' => 'Todos os favoritos foram removidos']);

        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id]);
    }

    #[Test]
    public function clear_favorites_only_removes_user_favorites()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $companion1 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion2 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion3 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user1->createToken('auth-token')->plainTextToken;

        // Favoritos do usuário 1
        Favorite::factory()->create([
            'user_id' => $user1->id,
            'companion_profile_id' => $companion1->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user1->id,
            'companion_profile_id' => $companion2->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user1->id,
            'companion_profile_id' => $companion3->id
        ]);

        // Favoritos do usuário 2
        Favorite::factory()->create([
            'user_id' => $user2->id,
            'companion_profile_id' => $companion1->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user2->id,
            'companion_profile_id' => $companion2->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites/clear');

        $response->assertStatus(200);

        // Verificar que apenas os favoritos do usuário 1 foram removidos
        $this->assertDatabaseMissing('favorites', ['user_id' => $user1->id]);
        $this->assertDatabaseHas('favorites', ['user_id' => $user2->id]);
    }

    // ============================================================================
    // TESTES AUTENTICADOS - VERIFICAR SE É FAVORITO
    // ============================================================================

    #[Test]
    public function can_check_if_companion_is_favorite()
    {
        $user = $this->createUser();
        $companion = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        // Adicionar como favorito via toggle
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites/toggle', [
            'companion_profile_id' => $companion->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/companions/{$companion->id}/is-favorite");

        $response->assertStatus(200);
        // Verificar que a resposta tem a estrutura esperada
        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('is_favorite', $response->json('data'));
    }

    #[Test]
    public function can_check_if_companion_is_not_favorite()
    {
        $user = $this->createUser();
        $companion = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/companions/{$companion->id}/is-favorite");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'is_favorite' => false
                    ]
                ]);
    }

    #[Test]
    public function check_favorite_requires_authentication()
    {
        $companion = CompanionProfile::factory()->create(['city_id' => $this->city->id]);

        $response = $this->getJson("/api/companions/{$companion->id}/is-favorite");

        $response->assertStatus(401);
    }

    // ============================================================================
    // TESTES AUTENTICADOS - ESTATÍSTICAS
    // ============================================================================

    #[Test]
    public function user_can_get_favorite_stats()
    {
        $user = $this->createUser();
        $companion1 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion2 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion3 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion4 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion5 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion1->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion2->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion3->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion4->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion5->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites/stats');

        $response->assertStatus(200);
    }

    // ============================================================================
    // TESTES DE PAGINAÇÃO
    // ============================================================================

    #[Test]
    public function favorites_are_paginated()
    {
        $user = $this->createUser();
        $companions = [];
        for ($i = 0; $i < 25; $i++) {
            $companions[] = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        }
        $token = $user->createToken('auth-token')->plainTextToken;

        foreach ($companions as $companion) {
            Favorite::factory()->create([
                'user_id' => $user->id,
                'companion_profile_id' => $companion->id
            ]);
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites?per_page=10');

        $response->assertStatus(200)
                ->assertJsonCount(10, 'data')
                ->assertJsonStructure([
                    'meta' => ['current_page', 'per_page', 'total', 'last_page']
                ]);
    }

    // ============================================================================
    // TESTES DE FILTROS
    // ============================================================================

    #[Test]
    public function can_filter_favorites_by_city()
    {
        $user = $this->createUser();
        $city1 = $this->createCity('Cidade A');
        $city2 = $this->createCity('Cidade B');
        $token = $user->createToken('auth-token')->plainTextToken;

        $companion1 = CompanionProfile::factory()->create(['city_id' => $city1->id]);
        $companion2 = CompanionProfile::factory()->create(['city_id' => $city2->id]);

        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion1->id
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion2->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/favorites?city_id={$city1->id}");

        $response->assertStatus(200);
        // Verificar que pelo menos um favorito foi retornado
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    #[Test]
    public function can_filter_favorites_by_date_range()
    {
        $user = $this->createUser();
        $companion1 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion2 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        // Criar favoritos com datas diferentes
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion1->id,
            'created_at' => now()->subDays(5)
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion2->id,
            'created_at' => now()->subDays(15)
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites?date_from=' . now()->subDays(10)->toDateString());

        $response->assertStatus(200);
        // Verificar que pelo menos um favorito foi retornado
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    // ============================================================================
    // TESTES DE ORDENAÇÃO
    // ============================================================================

    #[Test]
    public function can_sort_favorites_by_date()
    {
        $user = $this->createUser();
        $companion1 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $companion2 = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        $token = $user->createToken('auth-token')->plainTextToken;

        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion1->id,
            'created_at' => now()->subDays(5)
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion2->id,
            'created_at' => now()->subDays(10)
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites?sort_by=created_at&sort_order=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertTrue(
            strtotime($data[0]['created_at']) > strtotime($data[1]['created_at'])
        );
    }

    // ============================================================================
    // TESTES DE VALIDAÇÃO
    // ============================================================================

    #[Test]
    public function favorite_creation_requires_companion_id()
    {
        $user = $this->createUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['companion_profile_id']);
    }

    #[Test]
    public function favorite_creation_requires_valid_companion()
    {
        $user = $this->createUser();
        $token = $user->createToken('auth-token')->plainTextToken;

        $favoriteData = [
            'companion_profile_id' => 999
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites', $favoriteData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['companion_profile_id']);
    }

    // ============================================================================
    // TESTES DE PERFORMANCE
    // ============================================================================

    #[Test]
    public function favorites_endpoint_responds_quickly()
    {
        $user = $this->createUser();
        $companions = [];
        for ($i = 0; $i < 50; $i++) {
            $companions[] = CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        }
        $token = $user->createToken('auth-token')->plainTextToken;

        foreach ($companions as $companion) {
            Favorite::factory()->create([
                'user_id' => $user->id,
                'companion_profile_id' => $companion->id
            ]);
        }

        $startTime = microtime(true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites');
        $response->assertStatus(200);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(1.0, $executionTime, 'Favorites endpoint should respond within 1 second');
    }

    // ============================================================================
    // TESTES DE AUTENTICAÇÃO
    // ============================================================================

    #[Test]
    public function unauthenticated_user_cannot_access_favorite_routes()
    {
        $response = $this->getJson('/api/favorites');
        $response->assertStatus(401);

        $response = $this->postJson('/api/favorites');
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/favorites/1');
        $response->assertStatus(401);

        $response = $this->postJson('/api/favorites/toggle');
        $response->assertStatus(401);

        $response = $this->postJson('/api/favorites/clear');
        $response->assertStatus(401);

        $response = $this->getJson('/api/favorites/stats');
        $response->assertStatus(401);
    }
}
