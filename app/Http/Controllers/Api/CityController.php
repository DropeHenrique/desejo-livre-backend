<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    /**
     * Listar cidades
     */
    public function index(Request $request): JsonResponse
    {
        $query = City::with('state');

        if ($request->state_id) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->state_uf) {
            $query->whereHas('state', function ($q) use ($request) {
                $q->where('uf', strtoupper($request->state_uf));
            });
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->with_companions) {
            $query->withCount('companionProfiles');
        }

        $cities = $query->orderBy('name')->paginate($request->per_page ?? 50);

        return response()->json([
            'data' => $cities->items(),
            'meta' => [
                'current_page' => $cities->currentPage(),
                'per_page' => $cities->perPage(),
                'total' => $cities->total(),
                'last_page' => $cities->lastPage(),
            ]
        ]);
    }

    /**
     * Mostrar cidade específica
     */
    public function show(City $city): JsonResponse
    {
        $city->load(['state', 'districts']);

        return response()->json([
            'city' => $city
        ]);
    }

    /**
     * Buscar cidades por estado
     */
    public function byState(State $state): JsonResponse
    {
        $cities = $state->cities()
                       ->orderBy('name')
                       ->get();

        return response()->json([
            'data' => $cities
        ]);
    }

    /**
     * Buscar cidades populares (com mais acompanhantes)
     */
    public function popular(Request $request): JsonResponse
    {
        $cities = City::withCount('companionProfiles')
                     ->orderBy('companion_profiles_count', 'desc')
                     ->orderBy('name')
                     ->limit($request->limit ?? 10)
                     ->get()
                     ->map(function ($city) {
                         $city->popularity_score = $city->companion_profiles_count;
                         return $city;
                     });

        return response()->json([
            'data' => $cities
        ]);
    }

    /**
     * Buscar cidades por termo
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->get('q') ?? $request->get('term');

        if (!$term || strlen($term) < 2) {
            return response()->json([
                'data' => []
            ]);
        }

        $cities = City::with('state')
                     ->where(function ($query) use ($term) {
                         $query->where('name', 'like', "%{$term}%")
                               ->orWhereHas('state', function ($q) use ($term) {
                                   $q->where('name', 'like', "%{$term}%")
                                     ->orWhere('uf', 'like', "%{$term}%");
                               });
                     })
                     ->orderBy('name')
                     ->limit(10)
                     ->get();

        return response()->json([
            'data' => $cities
        ]);
    }

    /**
     * Listar distritos de uma cidade
     */
    public function districts($cityId, Request $request): JsonResponse
    {
        $city = City::findOrFail($cityId);
        $query = $city->districts()->orderBy('name');
        $perPage = $request->per_page ?? 50;
        $districts = $query->paginate($perPage);

        return response()->json([
            'data' => $districts->items(),
            'meta' => [
                'current_page' => $districts->currentPage(),
                'per_page' => $districts->perPage(),
                'total' => $districts->total(),
                'last_page' => $districts->lastPage(),
            ],
            'links' => [
                'first' => $districts->url(1),
                'last' => $districts->url($districts->lastPage()),
                'prev' => $districts->previousPageUrl(),
                'next' => $districts->nextPageUrl(),
            ]
        ]);
    }
}
