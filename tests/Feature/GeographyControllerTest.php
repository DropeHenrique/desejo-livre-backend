<?php

namespace Tests\Feature;

use App\Models\State;
use App\Models\City;
use App\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GeographyControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // ============================================================================
    // TESTES DE ESTADOS
    // ============================================================================

    #[Test]
    public function can_list_all_states()
    {
        State::factory()->count(5)->create();

        $response = $this->getJson('/api/geography/states');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'uf', 'created_at', 'updated_at']
                    ]
                ]);
    }

    #[Test]
    public function can_view_specific_state()
    {
        $state = State::factory()->create([
            'name' => 'São Paulo',
            'uf' => 'SP'
        ]);

        $response = $this->getJson("/api/geography/states/{$state->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'state' => ['id', 'name', 'uf', 'created_at', 'updated_at']
                ])
                ->assertJson([
                    'state' => [
                        'id' => $state->id,
                        'name' => 'São Paulo',
                        'uf' => 'SP'
                    ]
                ]);
    }

    #[Test]
    public function can_get_cities_of_state()
    {
        $state = State::factory()->create();
        City::factory()->count(3)->create(['state_id' => $state->id]);

        $response = $this->getJson("/api/geography/states/{$state->id}/cities");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'state_id', 'created_at', 'updated_at']
                    ]
                ])
                ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function cannot_view_nonexistent_state()
    {
        $response = $this->getJson('/api/geography/states/999');

        $response->assertStatus(404);
    }

    // ============================================================================
    // TESTES DE CIDADES
    // ============================================================================

    #[Test]
    public function can_list_all_cities()
    {
        City::factory()->count(5)->create();

        $response = $this->getJson('/api/geography/cities');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'state_id', 'created_at', 'updated_at']
                    ]
                ]);
    }

    #[Test]
    public function can_view_specific_city()
    {
        $state = State::factory()->create();
        $city = City::factory()->create([
            'name' => 'São Paulo',
            'state_id' => $state->id
        ]);

        $response = $this->getJson("/api/geography/cities/{$city->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'city' => [
                        'id', 'name', 'state_id', 'created_at', 'updated_at',
                        'state' => ['id', 'name', 'uf']
                    ]
                ])
                ->assertJson([
                    'city' => [
                        'id' => $city->id,
                        'name' => 'São Paulo',
                        'state_id' => $state->id
                    ]
                ]);
    }

    #[Test]
    public function can_get_cities_by_state()
    {
        $state1 = State::factory()->create();
        $state2 = State::factory()->create();

        City::factory()->count(3)->create(['state_id' => $state1->id]);
        City::factory()->count(2)->create(['state_id' => $state2->id]);

        $response = $this->getJson("/api/geography/cities/by-state/{$state1->id}");

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function can_get_popular_cities()
    {
        City::factory()->count(10)->create();

        $response = $this->getJson('/api/geography/cities/popular');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'state_id', 'popularity_score']
                    ]
                ]);
    }

    #[Test]
    public function can_search_cities()
    {
        City::factory()->create(['name' => 'São Paulo']);
        City::factory()->create(['name' => 'São José']);
        City::factory()->create(['name' => 'Rio de Janeiro']);

        $response = $this->getJson('/api/geography/cities/search?q=São');

        $response->assertStatus(200)
                ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function cannot_view_nonexistent_city()
    {
        $response = $this->getJson('/api/geography/cities/999');

        $response->assertStatus(404);
    }

    // ============================================================================
    // TESTES DE BAIRROS
    // ============================================================================

    #[Test]
    public function can_list_all_districts()
    {
        for ($i = 0; $i < 5; $i++) {
            $state = State::factory()->create(['uf' => 'UF_DISTLIST' . $i]);
            $city = City::factory()->create(['state_id' => $state->id]);
            District::factory()->create(['city_id' => $city->id]);
        }

        $response = $this->getJson('/api/geography/districts');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'city_id', 'created_at', 'updated_at']
                    ]
                ]);
    }

    #[Test]
    public function can_view_specific_district()
    {
        $state = State::factory()->create();
        $city = City::factory()->create(['state_id' => $state->id]);
        $district = District::factory()->create([
            'name' => 'Centro',
            'city_id' => $city->id
        ]);

        $response = $this->getJson("/api/geography/districts/{$district->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'district' => [
                        'id', 'name', 'city_id', 'created_at', 'updated_at',
                        'city' => ['id', 'name', 'state' => ['id', 'name', 'uf']]
                    ]
                ])
                ->assertJson([
                    'district' => [
                        'id' => $district->id,
                        'name' => 'Centro',
                        'city_id' => $city->id
                    ]
                ]);
    }

    #[Test]
    public function can_get_districts_by_city()
    {
        $state = State::factory()->create();
        $city1 = City::factory()->create(['state_id' => $state->id]);
        $city2 = City::factory()->create(['state_id' => $state->id]);

        District::factory()->count(3)->create(['city_id' => $city1->id]);
        District::factory()->count(2)->create(['city_id' => $city2->id]);

        $response = $this->getJson("/api/geography/districts/by-city/{$city1->id}");

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function can_search_districts()
    {
        $state = State::factory()->create();
        $city = City::factory()->create(['state_id' => $state->id]);

        District::factory()->create(['name' => 'Centro', 'city_id' => $city->id]);
        District::factory()->create(['name' => 'Vila Madalena', 'city_id' => $city->id]);
        District::factory()->create(['name' => 'Pinheiros', 'city_id' => $city->id]);

        $response = $this->getJson('/api/geography/districts/search?q=Centro');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function cannot_view_nonexistent_district()
    {
        $response = $this->getJson('/api/geography/districts/999');

        $response->assertStatus(404);
    }

    // ============================================================================
    // TESTES DE PAGINAÇÃO
    // ============================================================================

    #[Test]
    public function states_are_paginated()
    {
        State::factory()->count(25)->create();

        $response = $this->getJson('/api/geography/states?per_page=10');

        $response->assertStatus(200)
                ->assertJsonCount(10, 'data')
                ->assertJsonStructure([
                    'meta' => ['current_page', 'per_page', 'total', 'last_page']
                ]);
    }

    #[Test]
    public function districts_are_paginated()
    {
        for ($i = 0; $i < 25; $i++) {
            $state = State::factory()->create(['uf' => 'UF_DIST' . $i]);
            $city = City::factory()->create(['state_id' => $state->id]);
            District::factory()->create(['city_id' => $city->id]);
        }

        $response = $this->getJson('/api/geography/districts?per_page=10');

        $response->assertStatus(200)
                ->assertJsonCount(10, 'data')
                ->assertJsonStructure([
                    'meta' => ['current_page', 'per_page', 'total', 'last_page']
                ]);
    }

    #[Test]
    public function cities_are_paginated()
    {
        for ($i = 0; $i < 25; $i++) {
            $state = State::factory()->create(['uf' => 'UF_CITY' . $i]);
            City::factory()->create(['state_id' => $state->id]);
        }

        $response = $this->getJson('/api/geography/cities?per_page=10');

        $response->assertStatus(200)
                ->assertJsonCount(10, 'data')
                ->assertJsonStructure([
                    'meta' => ['current_page', 'per_page', 'total', 'last_page']
                ]);
    }

    // ============================================================================
    // TESTES DE ORDENAÇÃO
    // ============================================================================

    #[Test]
    public function states_can_be_sorted_by_name()
    {
        State::factory()->create(['name' => 'Rio de Janeiro']);
        State::factory()->create(['name' => 'São Paulo']);
        State::factory()->create(['name' => 'Minas Gerais']);

        $response = $this->getJson('/api/geography/states?sort_by=name&sort_order=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Minas Gerais', $data[0]['name']);
        $this->assertEquals('Rio de Janeiro', $data[1]['name']);
        $this->assertEquals('São Paulo', $data[2]['name']);
    }

    #[Test]
    public function cities_can_be_sorted_by_name()
    {
        $state = State::factory()->create();
        City::factory()->create(['name' => 'Rio de Janeiro', 'state_id' => $state->id]);
        City::factory()->create(['name' => 'São Paulo', 'state_id' => $state->id]);
        City::factory()->create(['name' => 'Belo Horizonte', 'state_id' => $state->id]);

        $response = $this->getJson('/api/geography/cities?sort_by=name&sort_order=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Belo Horizonte', $data[0]['name']);
        $this->assertEquals('Rio de Janeiro', $data[1]['name']);
        $this->assertEquals('São Paulo', $data[2]['name']);
    }

    // ============================================================================
    // TESTES DE FILTROS
    // ============================================================================

    #[Test]
    public function can_filter_cities_by_state_uf()
    {
        $state1 = State::factory()->create(['uf' => 'SP']);
        $state2 = State::factory()->create(['uf' => 'RJ']);

        City::factory()->count(3)->create(['state_id' => $state1->id]);
        City::factory()->count(2)->create(['state_id' => $state2->id]);

        $response = $this->getJson('/api/geography/cities?state_uf=SP');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function can_filter_districts_by_city_name()
    {
        $state = State::factory()->create();
        $city1 = City::factory()->create(['name' => 'São Paulo', 'state_id' => $state->id]);
        $city2 = City::factory()->create(['name' => 'Rio de Janeiro', 'state_id' => $state->id]);

        District::factory()->count(3)->create(['city_id' => $city1->id]);
        District::factory()->count(2)->create(['city_id' => $city2->id]);

        $response = $this->getJson('/api/geography/districts?city_name=São Paulo');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    // ============================================================================
    // TESTES DE PERFORMANCE
    // ============================================================================

    #[Test]
    public function geography_endpoints_respond_quickly()
    {
        State::factory()->count(5)->create();
        City::factory()->count(20)->create();
        District::factory()->count(50)->create();

        $startTime = microtime(true);

        $response = $this->getJson('/api/geography/states');
        $response->assertStatus(200);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(1.0, $executionTime, 'States endpoint should respond within 1 second');
    }

    #[Test]
    public function cities_with_state_relationship_loads_efficiently()
    {
        $state = State::factory()->create();
        City::factory()->count(20)->create(['state_id' => $state->id]);

        $response = $this->getJson("/api/geography/states/{$state->id}/cities");

        $response->assertStatus(200)
                ->assertJsonCount(20, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'state_id']
                    ]
                ]);
    }
}
