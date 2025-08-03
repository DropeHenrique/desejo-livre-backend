<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CompanionProfile;
use App\Models\District;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Busca cidades, bairros e estados por nome
     */
    public function cities(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q');

            if (!$query || strlen($query) < 3) {
                return response()->json([
                    'data' => [],
                    'message' => 'Query deve ter pelo menos 3 caracteres'
                ]);
            }

            $results = collect();

            // Buscar cidades
            $cities = City::with('state')
                ->where('name', 'ilike', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function ($city) {
                    return [
                        'id' => $city->id,
                        'name' => $city->name,
                        'type' => 'city',
                        'state' => $city->state,
                        'slug' => $city->slug,
                        'display_name' => $city->name . ' (' . $city->state->uf . ')'
                    ];
                });

            // Buscar bairros
            $districts = District::with(['city.state'])
                ->where('name', 'ilike', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function ($district) {
                    return [
                        'id' => $district->id,
                        'name' => $district->name,
                        'type' => 'district',
                        'city' => $district->city,
                        'state' => $district->city->state,
                        'slug' => $district->slug,
                        'display_name' => $district->name . ', ' . $district->city->name . ' (' . $district->city->state->uf . ')'
                    ];
                });

            // Buscar estados
            $states = State::where('name', 'ilike', "%{$query}%")
                ->orWhere('uf', 'ilike', "%{$query}%")
                ->limit(3)
                ->get()
                ->map(function ($state) {
                    return [
                        'id' => $state->id,
                        'name' => $state->name,
                        'type' => 'state',
                        'uf' => $state->uf,
                        'slug' => $state->slug,
                        'display_name' => $state->name . ' (' . $state->uf . ')'
                    ];
                });

            // Combinar e ordenar resultados
            $results = $cities->concat($districts)->concat($states)->take(10);

            return response()->json([
                'data' => $results->values(),
                'message' => 'Resultados encontrados com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar localizações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca acompanhantes
     */
    public function companions(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q');
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 12);

            if (!$query || strlen($query) < 2) {
                return response()->json([
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                    'from' => null,
                    'to' => null,
                    'message' => 'Query deve ter pelo menos 2 caracteres'
                ]);
            }

            $companions = CompanionProfile::with(['city.state', 'media', 'reviews'])
                ->where('verified', true)
                ->where(function ($q) use ($query) {
                    $q->where('artistic_name', 'ilike', "%{$query}%")
                      ->orWhere('about_me', 'ilike', "%{$query}%")
                      ->orWhereHas('city', function ($cityQuery) use ($query) {
                          $cityQuery->where('name', 'ilike', "%{$query}%");
                      })
                      ->orWhereHas('city.state', function ($stateQuery) use ($query) {
                          $stateQuery->where('name', 'ilike', "%{$query}%")
                                    ->orWhere('uf', 'ilike', "%{$query}%");
                      })
                      ->orWhereHas('districts', function ($districtQuery) use ($query) {
                          $districtQuery->where('name', 'ilike', "%{$query}%");
                      });
                })
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'data' => $companions->items(),
                'current_page' => $companions->currentPage(),
                'last_page' => $companions->lastPage(),
                'per_page' => $companions->perPage(),
                'total' => $companions->total(),
                'from' => $companions->firstItem(),
                'to' => $companions->lastItem(),
                'message' => 'Acompanhantes encontradas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar acompanhantes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
