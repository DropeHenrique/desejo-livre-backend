<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ViaCepService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CepController extends Controller
{
    public function __construct(
        private ViaCepService $viaCepService
    ) {}

    /**
     * Buscar endereço por CEP
     */
    public function searchByCep(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cep' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $cep = $request->cep;

        // Valida formato do CEP
        if (!$this->viaCepService->validateCep($cep)) {
            return response()->json([
                'message' => 'Formato de CEP inválido. Use apenas números (8 dígitos)'
            ], 422);
        }

        // Busca no ViaCEP
        $addressData = $this->viaCepService->searchByCep($cep);

        if (!$addressData) {
            return response()->json([
                'message' => 'CEP não encontrado'
            ], 404);
        }

        // Cria ou atualiza dados de localização no banco
        $locationData = $this->viaCepService->createOrUpdateLocationData($addressData);

        return response()->json([
            'message' => 'Endereço encontrado com sucesso',
            'data' => [
                'address' => $addressData,
                'location' => [
                    'state' => $locationData['state'] ? [
                        'id' => $locationData['state']->id,
                        'name' => $locationData['state']->name,
                        'uf' => $locationData['state']->uf,
                    ] : null,
                    'city' => $locationData['city'] ? [
                        'id' => $locationData['city']->id,
                        'name' => $locationData['city']->name,
                    ] : null,
                    'district' => $locationData['district'] ? [
                        'id' => $locationData['district']->id,
                        'name' => $locationData['district']->name,
                    ] : null,
                    'created' => $locationData['created'],
                    'error' => $locationData['error'],
                ],
                'formatted_cep' => $this->viaCepService->formatCep($cep)
            ]
        ]);
    }

    /**
     * Buscar CEPs por endereço
     */
    public function searchByAddress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'uf' => 'required|string|size:2',
            'city' => 'required|string|min:3|max:100',
            'street' => 'required|string|min:3|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $addresses = $this->viaCepService->searchByAddress(
            $request->uf,
            $request->city,
            $request->street
        );

        if (!$addresses) {
            return response()->json([
                'message' => 'Nenhum endereço encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Endereços encontrados com sucesso',
            'data' => $addresses
        ]);
    }

    /**
     * Validar formato do CEP
     */
    public function validateCep(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cep' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $isValid = $this->viaCepService->validateCep($request->cep);
        $formattedCep = $this->viaCepService->formatCep($request->cep);

        return response()->json([
            'data' => [
                'is_valid' => $isValid,
                'formatted_cep' => $formattedCep,
                'original_cep' => $request->cep
            ]
        ]);
    }

    /**
     * Buscar CEP e atualizar localização automaticamente
     */
    public function searchAndUpdateLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cep' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $cep = $request->cep;

        // Valida formato do CEP
        if (!$this->viaCepService->validateCep($cep)) {
            return response()->json([
                'message' => 'Formato de CEP inválido. Use apenas números (8 dígitos)'
            ], 422);
        }

        // Busca CEP e atualiza localização
        $locationData = $this->viaCepService->searchCepAndUpdateLocation($cep);

        if (!$locationData) {
            return response()->json([
                'message' => 'CEP não encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Localização atualizada com sucesso',
            'data' => [
                'location' => [
                    'state' => $locationData['state'] ? [
                        'id' => $locationData['state']->id,
                        'name' => $locationData['state']->name,
                        'uf' => $locationData['state']->uf,
                    ] : null,
                    'city' => $locationData['city'] ? [
                        'id' => $locationData['city']->id,
                        'name' => $locationData['city']->name,
                    ] : null,
                    'district' => $locationData['district'] ? [
                        'id' => $locationData['district']->id,
                        'name' => $locationData['district']->name,
                    ] : null,
                    'created' => $locationData['created'],
                    'error' => $locationData['error'],
                ],
                'formatted_cep' => $this->viaCepService->formatCep($cep),
                'new_records_created' => $locationData['created']
            ]
        ]);
    }
}
