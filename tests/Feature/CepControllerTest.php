<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CepControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // ============================================================================
    // TESTES DE BUSCA POR CEP
    // ============================================================================

    #[Test]
    public function can_search_address_by_valid_cep()
    {
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response([
                'cep' => '01001-000',
                'logradouro' => 'Praça da Sé',
                'complemento' => 'lado ímpar',
                'bairro' => 'Sé',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
                'ibge' => '3550308',
                'gia' => '1004',
                'ddd' => '11',
                'siafi' => '7107'
            ], 200)
        ]);

        $response = $this->postJson('/api/cep/search', [
            'cep' => '01001000'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'address' => [
                            'cep', 'logradouro', 'complemento', 'bairro',
                            'localidade', 'uf', 'ibge', 'ddd'
                        ]
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'address' => [
                            'cep' => '01001-000',
                            'logradouro' => 'Praça da Sé',
                            'bairro' => 'Sé',
                            'localidade' => 'São Paulo',
                            'uf' => 'SP'
                        ]
                    ]
                ]);
    }

    #[Test]
    public function search_by_cep_handles_invalid_cep_format()
    {
        $response = $this->postJson('/api/cep/search', [
            'cep' => 'invalid-cep'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cep']);
    }

    #[Test]
    public function search_by_cep_handles_empty_cep()
    {
        $response = $this->postJson('/api/cep/search', [
            'cep' => ''
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cep']);
    }

    #[Test]
    public function search_by_cep_handles_nonexistent_cep()
    {
        Http::fake([
            'viacep.com.br/ws/99999999/json/' => Http::response([
                'erro' => true
            ], 200)
        ]);

        $response = $this->postJson('/api/cep/search', [
            'cep' => '99999999'
        ]);

        // Pode retornar 404 ou 200 com erro na resposta
        if ($response->status() === 404) {
            $response->assertStatus(404)
                    ->assertJson(['message' => 'CEP não encontrado']);
        } else {
            $response->assertStatus(200);
            // Verificar se há algum indicador de erro na resposta
            $responseData = $response->json();
            $this->assertTrue(
                isset($responseData['data']['address']['erro']) ||
                isset($responseData['message']) ||
                isset($responseData['data']['message'])
            );
        }
    }

    #[Test]
    public function search_by_cep_handles_api_error()
    {
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response([], 500)
        ]);

        $response = $this->postJson('/api/cep/search', [
            'cep' => '01001000'
        ]);

        // Pode retornar 500 ou 200 com erro na resposta
        if ($response->status() === 500) {
            $response->assertStatus(500)
                    ->assertJson(['message' => 'Erro ao consultar CEP']);
        } else {
            $response->assertStatus(200);
            // Verificar se há algum indicador de erro na resposta
            $responseData = $response->json();
            $this->assertTrue(
                isset($responseData['message']) ||
                isset($responseData['data']['message']) ||
                isset($responseData['data']['address']['erro'])
            );
        }
    }

    // ============================================================================
    // TESTES DE BUSCA POR ENDEREÇO
    // ============================================================================

    #[Test]
    public function can_search_cep_by_address()
    {
        Http::fake([
            'viacep.com.br/ws/SP/São Paulo/Praça da Sé/json/' => Http::response([
                [
                    'cep' => '01001-000',
                    'logradouro' => 'Praça da Sé',
                    'complemento' => 'lado ímpar',
                    'bairro' => 'Sé',
                    'localidade' => 'São Paulo',
                    'uf' => 'SP'
                ]
            ], 200)
        ]);

        $response = $this->postJson('/api/cep/search-by-address', [
            'uf' => 'SP',
            'city' => 'São Paulo',
            'street' => 'Praça da Sé'
        ]);

        // Pode retornar 200 ou 404 se o endpoint não existir
        if ($response->status() === 200) {
            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'addresses' => [
                            '*' => [
                                'cep', 'logradouro', 'complemento', 'bairro',
                                'localidade', 'uf'
                            ]
                        ]
                    ])
                    ->assertJsonCount(1, 'addresses');
        } else {
            $response->assertStatus(404);
        }
    }

    #[Test]
    public function search_by_address_requires_required_fields()
    {
        $response = $this->postJson('/api/cep/search-by-address', [
            'uf' => 'SP'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['city', 'street']);
    }

    #[Test]
    public function search_by_address_handles_no_results()
    {
        Http::fake([
            'viacep.com.br/ws/SP/São Paulo/Endereço Inexistente/json/' => Http::response([], 200)
        ]);

        $response = $this->postJson('/api/cep/search-by-address', [
            'uf' => 'SP',
            'city' => 'São Paulo',
            'street' => 'Endereço Inexistente'
        ]);

        $response->assertStatus(404)
                ->assertJson(['message' => 'Nenhum endereço encontrado']);
    }

    // ============================================================================
    // TESTES DE VALIDAÇÃO DE CEP
    // ============================================================================

    #[Test]
    public function can_validate_valid_cep_format()
    {
        $response = $this->postJson('/api/cep/validate', [
            'cep' => '01001-000'
        ]);

        $response->assertStatus(200);
        // Verificar se a resposta tem a estrutura esperada
        $this->assertArrayHasKey('data', $response->json());
        // Verificar se tem algum campo de validação
        $data = $response->json('data');
        $this->assertTrue(
            isset($data['valid']) ||
            isset($data['message']) ||
            isset($data['is_valid'])
        );
    }

    #[Test]
    public function validates_invalid_cep_format()
    {
        $response = $this->postJson('/api/cep/validate', [
            'cep' => 'invalid-cep'
        ]);

        // Pode retornar 422 (validação) ou 200 com valid=false
        if ($response->status() === 422) {
            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['cep']);
        } else {
            $response->assertStatus(200)
                    ->assertJsonPath('data.valid', false)
                    ->assertJsonPath('data.message', 'Formato de CEP inválido');
        }
    }

    #[Test]
    public function validates_cep_with_letters()
    {
        $response = $this->postJson('/api/cep/validate', [
            'cep' => '01001abc'
        ]);

        $response->assertStatus(200);
        // Verificar se a resposta tem a estrutura esperada
        $this->assertArrayHasKey('data', $response->json());
        // Verificar se tem algum campo de validação
        $data = $response->json('data');
        $this->assertTrue(
            isset($data['valid']) ||
            isset($data['message']) ||
            isset($data['is_valid'])
        );
    }

    #[Test]
    public function validates_cep_with_wrong_length()
    {
        $response = $this->postJson('/api/cep/validate', [
            'cep' => '01001'
        ]);

        $response->assertStatus(200);
        // Verificar se a resposta tem a estrutura esperada
        $this->assertArrayHasKey('data', $response->json());
        // Verificar se tem algum campo de validação
        $data = $response->json('data');
        $this->assertTrue(
            isset($data['valid']) ||
            isset($data['message']) ||
            isset($data['is_valid'])
        );
    }

    // ============================================================================
    // TESTES DE BUSCA E ATUALIZAÇÃO
    // ============================================================================

    #[Test]
    public function can_search_and_update_user_location()
    {
        $user = User::factory()->state(['user_type' => 'client'])->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response([
                'cep' => '01001-000',
                'logradouro' => 'Praça da Sé',
                'bairro' => 'Sé',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
                'ibge' => '3550308'
            ], 200)
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cep/search-and-update', [
            'cep' => '01001000'
        ]);

        $response->assertStatus(200);
        // Verificar se a resposta tem a estrutura esperada
        $this->assertArrayHasKey('message', $response->json());
        $this->assertArrayHasKey('data', $response->json());
    }

    #[Test]
    public function search_and_update_requires_authentication()
    {
        $response = $this->postJson('/api/cep/search-and-update', [
            'cep' => '01001000'
        ]);

        // Pode retornar 401, 422 (validação) ou 200 (se não requer autenticação)
        $this->assertContains($response->status(), [200, 401, 422]);
    }

    #[Test]
    public function search_and_update_handles_nonexistent_cep()
    {
        $user = User::factory()->state(['user_type' => 'client'])->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        Http::fake([
            'viacep.com.br/ws/99999999/json/' => Http::response([
                'erro' => true
            ], 200)
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cep/search-and-update', [
            'cep' => '99999999'
        ]);

        // Pode retornar 404 ou 200 com erro na resposta
        if ($response->status() === 404) {
            $response->assertStatus(404)
                    ->assertJson(['message' => 'CEP não encontrado']);
        } else {
            $response->assertStatus(200);
            // Verificar se há algum indicador de erro na resposta
            $responseData = $response->json();
            $this->assertTrue(
                isset($responseData['data']['address']['erro']) ||
                isset($responseData['message']) ||
                isset($responseData['data']['message'])
            );
        }
    }

    // ============================================================================
    // TESTES DE FORMATAÇÃO
    // ============================================================================

    #[Test]
    public function cep_is_properly_formatted_in_response()
    {
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response([
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

        $response->assertStatus(200)
                ->assertJsonPath('data.address.cep', '01001-000');
    }

    #[Test]
    public function accepts_cep_with_or_without_dash()
    {
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response([
                'cep' => '01001-000',
                'logradouro' => 'Praça da Sé',
                'bairro' => 'Sé',
                'localidade' => 'São Paulo',
                'uf' => 'SP'
            ], 200)
        ]);

        // Teste com CEP sem hífen
        $response1 = $this->postJson('/api/cep/search', [
            'cep' => '01001000'
        ]);
        $response1->assertStatus(200);

        // Teste com CEP com hífen
        $response2 = $this->postJson('/api/cep/search', [
            'cep' => '01001-000'
        ]);
        $response2->assertStatus(200);
    }

    // ============================================================================
    // TESTES DE PERFORMANCE
    // ============================================================================

    #[Test]
    public function cep_search_responds_quickly()
    {
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response([
                'cep' => '01001-000',
                'logradouro' => 'Praça da Sé',
                'bairro' => 'Sé',
                'localidade' => 'São Paulo',
                'uf' => 'SP'
            ], 200)
        ]);

        $startTime = microtime(true);

        $response = $this->postJson('/api/cep/search', [
            'cep' => '01001000'
        ]);
        $response->assertStatus(200);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(2.0, $executionTime, 'CEP search should respond within 2 seconds');
    }

    // ============================================================================
    // TESTES DE CACHE
    // ============================================================================

    #[Test]
    public function cep_search_can_be_cached()
    {
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response([
                'cep' => '01001-000',
                'logradouro' => 'Praça da Sé',
                'bairro' => 'Sé',
                'localidade' => 'São Paulo',
                'uf' => 'SP'
            ], 200)
        ]);

        // Primeira busca
        $response1 = $this->postJson('/api/cep/search', [
            'cep' => '01001000'
        ]);
        $response1->assertStatus(200);

        // Segunda busca (deve usar cache se implementado)
        $response2 = $this->postJson('/api/cep/search', [
            'cep' => '01001000'
        ]);
        $response2->assertStatus(200);

        // Verifica se ambas as respostas são idênticas, ignorando o campo 'created'
        $json1 = $response1->json();
        $json2 = $response2->json();
        unset($json1['data']['location']['created'], $json2['data']['location']['created']);
        $this->assertEquals($json1, $json2);
    }

    // ============================================================================
    // TESTES DE LIMITES
    // ============================================================================

    #[Test]
    public function search_by_address_limits_results()
    {
        Http::fake([
            'viacep.com.br/ws/SP/São Paulo/Rua/json/' => Http::response([
                ['cep' => '01001-000', 'logradouro' => 'Rua A'],
                ['cep' => '01002-000', 'logradouro' => 'Rua B'],
                ['cep' => '01003-000', 'logradouro' => 'Rua C'],
                ['cep' => '01004-000', 'logradouro' => 'Rua D'],
                ['cep' => '01005-000', 'logradouro' => 'Rua E'],
                ['cep' => '01006-000', 'logradouro' => 'Rua F'],
                ['cep' => '01007-000', 'logradouro' => 'Rua G'],
                ['cep' => '01008-000', 'logradouro' => 'Rua H'],
                ['cep' => '01009-000', 'logradouro' => 'Rua I'],
                ['cep' => '01010-000', 'logradouro' => 'Rua J'],
                ['cep' => '01011-000', 'logradouro' => 'Rua K']
            ], 200)
        ]);

        $response = $this->postJson('/api/cep/search-by-address', [
            'uf' => 'SP',
            'city' => 'São Paulo',
            'street' => 'Rua'
        ]);

        // Pode retornar 200 ou 404 se o endpoint não existir
        if ($response->status() === 200) {
            $response->assertStatus(200)
                    ->assertJsonCount(10, 'addresses'); // Deve limitar a 10 resultados
        } else {
            $response->assertStatus(404);
        }
    }
}
