<?php

namespace App\Traits;

use App\Services\ViaCepService;

trait HasAddress
{
    /**
     * Atualiza endereço baseado no CEP
     */
    public function updateAddressFromCep(string $cep): bool
    {
        try {
            $viaCepService = app(ViaCepService::class);

            // Busca dados do CEP
            $addressData = $viaCepService->searchByCep($cep);

            if (!$addressData) {
                return false;
            }

            // Cria ou atualiza dados de localização
            $locationData = $viaCepService->createOrUpdateLocationData($addressData);

            if (!$locationData['state'] || !$locationData['city']) {
                return false;
            }

            // Atualiza os campos de endereço
            $this->update([
                'state_id' => $locationData['state']->id,
                'city_id' => $locationData['city']->id,
                'district_id' => $locationData['district']?->id,
                'address' => $addressData['logradouro'] ?? null,
                'complement' => $addressData['complemento'] ?? null,
                'cep' => $viaCepService->formatCep($cep),
            ]);

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Valida e formata CEP
     */
    public function validateAndFormatCep(string $cep): ?string
    {
        $viaCepService = app(ViaCepService::class);

        if (!$viaCepService->validateCep($cep)) {
            return null;
        }

        return $viaCepService->formatCep($cep);
    }

    /**
     * Verifica se o endereço está completo
     */
    public function hasCompleteAddress(): bool
    {
        return !empty($this->cep) &&
               !empty($this->city_id) &&
               !empty($this->state_id);
    }

    /**
     * Obtém endereço formatado
     */
    public function getFormattedAddress(): string
    {
        $parts = [];

        if ($this->address) {
            $parts[] = $this->address;
        }

        if ($this->complement) {
            $parts[] = $this->complement;
        }

        if ($this->district?->name) {
            $parts[] = $this->district->name;
        }

        if ($this->city?->name) {
            $parts[] = $this->city->name;
        }

        if ($this->state?->uf) {
            $parts[] = $this->state->uf;
        }

        if ($this->cep) {
            $parts[] = $this->cep;
        }

        return implode(', ', array_filter($parts));
    }

    /**
     * Obtém endereço resumido (cidade, estado)
     */
    public function getShortAddress(): string
    {
        $parts = [];

        if ($this->city?->name) {
            $parts[] = $this->city->name;
        }

        if ($this->state?->uf) {
            $parts[] = $this->state->uf;
        }

        return implode(' - ', array_filter($parts));
    }
}
