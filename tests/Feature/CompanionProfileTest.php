<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CompanionProfile;
use App\Models\City;
use App\Models\State;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanionProfileTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar dados básicos para testes
        $this->state = State::factory()->create();
        $this->city = City::factory()->create(['state_id' => $this->state->id]);
        $this->plan = Plan::factory()->create(['user_type' => 'companion']);
    }

    /** @test */
    public function can_list_companion_profiles()
    {
        // Criar alguns perfis de teste
        CompanionProfile::factory()->count(3)->create([
            'city_id' => $this->city->id,
            'verified' => true
        ]);

        $response = $this->getJson('/api/companions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'artistic_name',
                            'slug',
                            'age',
                            'verified',
                            'online_status',
                            'city' => ['name'],
                            'average_rating'
                        ]
                    ],
                    'links',
                    'meta'
                ]);
    }

    /** @test */
    public function can_view_single_companion_profile()
    {
        $profile = CompanionProfile::factory()->create([
            'city_id' => $this->city->id,
            'verified' => true
        ]);

        $response = $this->getJson("/api/companions/{$profile->slug}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'artistic_name',
                        'about_me',
                        'age',
                        'height',
                        'weight',
                        'eye_color',
                        'hair_color',
                        'verified',
                        'online_status',
                        'city',
                        'services',
                        'reviews'
                    ]
                ]);
    }

    /** @test */
    public function companion_can_view_own_profile()
    {
        $user = User::factory()->create(['user_type' => 'companion']);
        $profile = CompanionProfile::factory()->create([
            'user_id' => $user->id,
            'city_id' => $this->city->id
        ]);

        $response = $this->actingAs($user, 'companion')
                         ->getJson('/api/companion/my-profile');

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $profile->id,
                        'artistic_name' => $profile->artistic_name
                    ]
                ]);
    }

    /** @test */
    public function companion_can_update_profile()
    {
        $user = User::factory()->create(['user_type' => 'companion']);
        $profile = CompanionProfile::factory()->create([
            'user_id' => $user->id,
            'city_id' => $this->city->id
        ]);

        $updateData = [
            'artistic_name' => 'Novo Nome Artístico',
            'about_me' => 'Nova descrição sobre mim',
            'age' => 25,
            'height' => 170,
            'weight' => 60,
            'eye_color' => 'castanhos',
            'hair_color' => 'loiro'
        ];

        $response = $this->actingAs($user, 'companion')
                         ->putJson('/api/companion/my-profile', $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('companion_profiles', [
            'id' => $profile->id,
            'artistic_name' => 'Novo Nome Artístico',
            'about_me' => 'Nova descrição sobre mim',
            'age' => 25
        ]);
    }

    /** @test */
    public function companion_can_toggle_online_status()
    {
        $user = User::factory()->create(['user_type' => 'companion']);
        $profile = CompanionProfile::factory()->create([
            'user_id' => $user->id,
            'city_id' => $this->city->id,
            'online_status' => false
        ]);

        // Ficar online
        $response = $this->actingAs($user, 'companion')
                         ->postJson('/api/companion/online');

        $response->assertStatus(200);
        $this->assertDatabaseHas('companion_profiles', [
            'id' => $profile->id,
            'online_status' => true
        ]);

        // Ficar offline
        $response = $this->actingAs($user, 'companion')
                         ->postJson('/api/companion/offline');

        $response->assertStatus(200);
        $this->assertDatabaseHas('companion_profiles', [
            'id' => $profile->id,
            'online_status' => false
        ]);
    }

    /** @test */
    public function client_can_add_companion_to_favorites()
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $profile = CompanionProfile::factory()->create([
            'city_id' => $this->city->id,
            'verified' => true
        ]);

        $response = $this->actingAs($client, 'client')
                         ->postJson("/api/companions/{$profile->id}/favorite");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Added to favorites']);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $client->id,
            'companion_profile_id' => $profile->id
        ]);
    }

    /** @test */
    public function client_can_remove_companion_from_favorites()
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $profile = CompanionProfile::factory()->create([
            'city_id' => $this->city->id,
            'verified' => true
        ]);

        // Primeiro adicionar aos favoritos
        $client->favorites()->create(['companion_profile_id' => $profile->id]);

        $response = $this->actingAs($client, 'client')
                         ->deleteJson("/api/companions/{$profile->id}/favorite");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Removed from favorites']);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $client->id,
            'companion_profile_id' => $profile->id
        ]);
    }

    /** @test */
    public function client_can_review_companion()
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $profile = CompanionProfile::factory()->create([
            'city_id' => $this->city->id,
            'verified' => true
        ]);

        $reviewData = [
            'rating' => 5,
            'comment' => 'Excelente atendimento!',
            'is_anonymous' => false
        ];

        $response = $this->actingAs($client, 'client')
                         ->postJson("/api/companions/{$profile->id}/review", $reviewData);

        $response->assertStatus(201)
                ->assertJson(['message' => 'Review submitted successfully']);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $client->id,
            'companion_profile_id' => $profile->id,
            'rating' => 5,
            'comment' => 'Excelente atendimento!'
        ]);
    }

    /** @test */
    public function can_filter_companions_by_city()
    {
        $otherCity = City::factory()->create(['state_id' => $this->state->id]);

        CompanionProfile::factory()->create(['city_id' => $this->city->id]);
        CompanionProfile::factory()->create(['city_id' => $otherCity->id]);

        $response = $this->getJson("/api/companions?city_id={$this->city->id}");

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function can_filter_companions_by_verified_status()
    {
        CompanionProfile::factory()->create([
            'city_id' => $this->city->id,
            'verified' => true
        ]);
        CompanionProfile::factory()->create([
            'city_id' => $this->city->id,
            'verified' => false
        ]);

        $response = $this->getJson('/api/companions?verified=1');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function can_filter_companions_by_online_status()
    {
        CompanionProfile::factory()->create([
            'city_id' => $this->city->id,
            'online_status' => true
        ]);
        CompanionProfile::factory()->create([
            'city_id' => $this->city->id,
            'online_status' => false
        ]);

        $response = $this->getJson('/api/companions?online=1');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function admin_can_verify_companion_profile()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $profile = CompanionProfile::factory()->create([
            'city_id' => $this->city->id,
            'verified' => false
        ]);

        $response = $this->actingAs($admin, 'admin')
                         ->postJson("/api/companions/{$profile->id}/verify");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Profile verified successfully']);

        $this->assertDatabaseHas('companion_profiles', [
            'id' => $profile->id,
            'verified' => true
        ]);
    }

    /** @test */
    public function only_admin_can_verify_profiles()
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $profile = CompanionProfile::factory()->create([
            'city_id' => $this->city->id,
            'verified' => false
        ]);

        $response = $this->actingAs($client, 'client')
                         ->postJson("/api/companions/{$profile->id}/verify");

        $response->assertStatus(403);
    }
}
