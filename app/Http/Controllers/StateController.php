<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StateController extends Controller
{
    /**
     * Listar todos os estados
     *
     * Retorna uma lista de todos os estados brasileiros disponíveis na plataforma.
     * É possível filtrar por UF ou buscar por nome.
     *
     * @group Geografia
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "São Paulo",
     *       "uf": "SP",
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $query = State::query();

        // Filtro por UF
        if ($request->has('uf')) {
            $query->byUf($request->uf);
        }

        // Busca por nome
        if ($request->has('search')) {
            $query->where('name', 'ILIKE', '%' . $request->search . '%');
        }

        $states = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $states
        ]);
    }

    /**
     * Exibir estado específico
     *
     * Retorna os detalhes de um estado específico, incluindo suas cidades.
     *
     * @group Geografia
     * @urlParam id int required ID do estado. Example: 1
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "São Paulo",
     *     "uf": "SP",
     *     "cities": [
     *       {
     *         "id": 1,
     *         "name": "São Paulo",
     *         "state_id": 1
     *       }
     *     ]
     *   }
     * }
     */
    public function show(string $id): JsonResponse
    {
        $state = State::with('cities')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $state
        ]);
    }

    /**
     * Listar cidades de um estado
     *
     * Retorna todas as cidades de um estado específico.
     *
     * @group Geografia
     * @urlParam state int required ID do estado. Example: 1
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "São Paulo",
     *       "state_id": 1,
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function cities(string $state): JsonResponse
    {
        $stateModel = State::findOrFail($state);
        $cities = $stateModel->cities()->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * Listar acompanhantes de um estado
     *
     * Retorna todos os perfis de acompanhantes verificados de um estado específico.
     *
     * @group Geografia
     * @urlParam state int required ID do estado. Example: 1
     * @queryParam per_page int Número de resultados por página (padrão: 15). Example: 10
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "artistic_name": "Maria Silva",
     *       "age": 25,
     *       "city": {
     *         "id": 1,
     *         "name": "São Paulo"
     *       }
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "total": 50
     *   }
     * }
     */
    public function companions(Request $request, string $state): JsonResponse
    {
        $stateModel = State::findOrFail($state);

        $query = $stateModel->companionProfiles()
                           ->with(['city', 'user'])
                           ->where('verified', true);

        $perPage = $request->per_page ?? 15;
        $companions = $query->paginate($perPage);

        return response()->json($companions);
    }
}
