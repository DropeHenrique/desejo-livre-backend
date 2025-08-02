<?php

namespace App\Services;

use App\Models\State;
use App\Models\City;
use App\Models\District;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ViaCepService
{
    private const BASE_URL = 'https://viacep.com.br/ws';

    /**
     * Busca informações de endereço por CEP
     */
    public function searchByCep(string $cep): ?array
    {
        // Remove caracteres não numéricos
        $cep = preg_replace('/[^0-9]/', '', $cep);

        // Valida formato do CEP
        if (strlen($cep) !== 8) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get(self::BASE_URL . "/{$cep}/json");

            if ($response->successful()) {
                $data = $response->json();

                // Verifica se o CEP foi encontrado
                if (isset($data['erro']) && $data['erro'] === true) {
                    return null;
                }

                return $this->formatAddressData($data);
            }
        } catch (\Exception $e) {
            // Silenciar erro de log por enquanto
            return null;
        }

        return null;
    }

    /**
     * Busca CEPs por endereço
     */
    public function searchByAddress(string $uf, string $city, string $street): ?array
    {
        // Validações básicas
        if (strlen($uf) !== 2 || strlen($city) < 3 || strlen($street) < 3) {
            return null;
        }

        try {
            $url = self::BASE_URL . "/" . Str::upper($uf) . "/" .
                   urlencode($city) . "/" .
                   urlencode($street) . "/json";

            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                $data = $response->json();

                // Se retornou array vazio ou erro
                if (empty($data) || (isset($data['erro']) && $data['erro'] === true)) {
                    return null;
                }

                // Retorna array de endereços encontrados
                return array_map([$this, 'formatAddressData'], $data);
            }
        } catch (\Exception $e) {
            // Silenciar erro de log por enquanto
            return null;
        }

        return null;
    }

    /**
     * Formata os dados do endereço
     */
    private function formatAddressData(array $data): array
    {
        return [
            'cep' => $data['cep'] ?? null,
            'logradouro' => $data['logradouro'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'localidade' => $data['localidade'] ?? null,
            'uf' => $data['uf'] ?? null,
            'estado' => $data['estado'] ?? null,
            'regiao' => $data['regiao'] ?? null,
            'ibge' => $data['ibge'] ?? null,
            'gia' => $data['gia'] ?? null,
            'ddd' => $data['ddd'] ?? null,
            'siafi' => $data['siafi'] ?? null,
        ];
    }

    /**
     * Cria ou atualiza cidade e bairro baseado nos dados do ViaCEP
     */
    public function createOrUpdateLocationData(array $addressData): array
    {
        $result = [
            'state' => null,
            'city' => null,
            'district' => null,
            'created' => false,
            'error' => null
        ];

        try {
            // Busca ou cria o estado
            $state = State::where('uf', Str::upper($addressData['uf']))->first();
            if (!$state) {
                // Se o estado não existe, vamos criá-lo
                $state = State::create([
                    'name' => $addressData['estado'] ?? $addressData['uf'],
                    'uf' => Str::upper($addressData['uf']),
                ]);
                $result['created'] = true;
            }
            $result['state'] = $state;

            // Busca ou cria a cidade
            $city = City::where('name', $addressData['localidade'])
                       ->where('state_id', $state->id)
                       ->first();

            if (!$city) {
                $city = City::create([
                    'name' => $addressData['localidade'],
                    'state_id' => $state->id,
                ]);
                $result['created'] = true;
            }
            $result['city'] = $city;

            // Busca ou cria o bairro (se existir)
            if (!empty($addressData['bairro'])) {
                $district = District::where('name', $addressData['bairro'])
                                   ->where('city_id', $city->id)
                                   ->first();

                if (!$district) {
                    $district = District::create([
                        'name' => $addressData['bairro'],
                        'city_id' => $city->id,
                    ]);
                    $result['created'] = true;
                }
                $result['district'] = $district;
            }

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Busca CEP e cria/atualiza dados de localização
     */
    public function searchCepAndUpdateLocation(string $cep): ?array
    {
        $addressData = $this->searchByCep($cep);

        if (!$addressData) {
            return null;
        }

        return $this->createOrUpdateLocationData($addressData);
    }

    /**
     * Valida formato do CEP
     */
    public function validateCep(string $cep): bool
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        return strlen($cep) === 8;
    }

    /**
     * Formata CEP para exibição
     */
    public function formatCep(string $cep): string
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);

        if (strlen($cep) === 8) {
            return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
        }

        return $cep;
    }
}
