<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    /**
     * Listar planos
     */
    public function index(Request $request): JsonResponse
    {
        $query = Plan::withCount('subscriptions');

        if ($request->user_type) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->active !== null) {
            $query->where('active', $request->active);
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $plans = $query->orderBy('price')->paginate($request->per_page ?? 50);

        return response()->json([
            'data' => $plans->items(),
            'meta' => [
                'current_page' => $plans->currentPage(),
                'per_page' => $plans->perPage(),
                'total' => $plans->total(),
                'last_page' => $plans->lastPage(),
            ]
        ]);
    }

    /**
     * Mostrar plano específico
     */
    public function show(Plan $plan): JsonResponse
    {
        return response()->json([
            'data' => $plan
        ]);
    }

    /**
     * Criar plano (Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Plan::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'user_type' => 'required|in:client,companion',
            'features' => 'nullable|array',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $plan = Plan::create($request->all());

        return response()->json([
            'message' => 'Plano criado com sucesso',
            'data' => $plan
        ], 201);
    }

    /**
     * Atualizar plano (Admin)
     */
    public function update(Request $request, Plan $plan): JsonResponse
    {
        $this->authorize('update', $plan);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'duration_days' => 'sometimes|integer|min:1',
            'user_type' => 'sometimes|in:client,companion',
            'features' => 'nullable|array',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $plan->update($request->all());

        return response()->json([
            'message' => 'Plano atualizado com sucesso',
            'data' => $plan->fresh()
        ]);
    }

    /**
     * Excluir plano (Admin)
     */
    public function destroy(Plan $plan): JsonResponse
    {
        $this->authorize('delete', $plan);

        // Verificar se há assinaturas ativas
        if ($plan->subscriptions()->active()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir um plano que possui assinaturas ativas'
            ], 422);
        }

        $plan->delete();

        return response()->json([
            'message' => 'Plano excluído com sucesso'
        ]);
    }

    /**
     * Buscar planos por tipo de usuário
     */
    public function byUserType(string $userType): JsonResponse
    {
        $plans = Plan::where('user_type', $userType)
                    ->where('active', true)
                    ->orderBy('price')
                    ->get();

        return response()->json([
            'data' => $plans
        ]);
    }

    /**
     * Comparar planos
     */
    public function compare(Request $request): JsonResponse
    {
        $planIds = $request->get('plan_ids', []);

        if (empty($planIds) || count($planIds) < 2) {
            return response()->json([
                'message' => 'É necessário selecionar pelo menos 2 planos para comparação'
            ], 422);
        }

        $plans = Plan::whereIn('id', $planIds)->get();

        return response()->json([
            'data' => $plans
        ]);
    }
}
