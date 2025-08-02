<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ServiceTypeController extends Controller
{
    /**
     * Listar tipos de serviço
     */
    public function index(Request $request): JsonResponse
    {
        $query = ServiceType::withCount('companionServices');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->popular) {
            $query->orderBy('companion_services_count', 'desc');
        } else {
            $query->orderBy('name');
        }

        $serviceTypes = $query->paginate($request->per_page ?? 50);

        return response()->json([
            'data' => $serviceTypes->items(),
            'meta' => [
                'current_page' => $serviceTypes->currentPage(),
                'per_page' => $serviceTypes->perPage(),
                'total' => $serviceTypes->total(),
                'last_page' => $serviceTypes->lastPage(),
            ]
        ]);
    }

    /**
     * Mostrar tipo de serviço específico
     */
    public function show(ServiceType $serviceType): JsonResponse
    {
        $serviceType->load(['companionServices.companionProfile']);

        return response()->json([
            'data' => $serviceType
        ]);
    }

    /**
     * Criar tipo de serviço (Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ServiceType::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:service_types',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceType = ServiceType::create($request->only(['name']));

        return response()->json([
            'message' => 'Tipo de serviço criado com sucesso',
            'data' => $serviceType
        ], 201);
    }

    /**
     * Atualizar tipo de serviço (Admin)
     */
    public function update(Request $request, ServiceType $serviceType): JsonResponse
    {
        $this->authorize('update', $serviceType);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:service_types,name,' . $serviceType->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceType->update($request->only(['name']));

        return response()->json([
            'message' => 'Tipo de serviço atualizado com sucesso',
            'data' => $serviceType->fresh()
        ]);
    }

    /**
     * Excluir tipo de serviço (Admin)
     */
    public function destroy(ServiceType $serviceType): JsonResponse
    {
        $this->authorize('delete', $serviceType);

        // Verificar se há serviços associados
        if ($serviceType->companionServices()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir um tipo de serviço que possui serviços associados'
            ], 422);
        }

        $serviceType->delete();

        return response()->json([
            'message' => 'Tipo de serviço excluído com sucesso'
        ]);
    }

    /**
     * Buscar tipos de serviço populares
     */
    public function popular(Request $request): JsonResponse
    {
        $serviceTypes = ServiceType::popular($request->limit ?? 10)->get();

        return response()->json([
            'data' => $serviceTypes
        ]);
    }
}
