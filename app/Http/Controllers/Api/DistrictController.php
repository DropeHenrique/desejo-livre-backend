<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DistrictController extends Controller
{
    /**
     * Listar bairros
     */
    public function index(Request $request): JsonResponse
    {
        $query = District::with('city.state');

        if ($request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->city_name) {
            $query->whereHas('city', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->city_name}%");
            });
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->with_companions) {
            $query->withCount('companionDistricts');
        }

        $districts = $query->orderBy('name')->paginate($request->per_page ?? 50);

        return response()->json([
            'data' => $districts->items(),
            'meta' => [
                'current_page' => $districts->currentPage(),
                'per_page' => $districts->perPage(),
                'total' => $districts->total(),
                'last_page' => $districts->lastPage(),
            ]
        ]);
    }

    /**
     * Mostrar bairro específico
     */
    public function show(District $district): JsonResponse
    {
        $district->load(['city.state']);

        return response()->json([
            'district' => $district
        ]);
    }

    /**
     * Buscar bairros por cidade
     */
    public function byCity(City $city): JsonResponse
    {
        $districts = $city->districts()
                         ->orderBy('name')
                         ->get();

        return response()->json([
            'data' => $districts
        ]);
    }

    /**
     * Buscar bairros por termo
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->get('q') ?? $request->get('term');
        $cityId = $request->get('city_id');

        if (!$term || strlen($term) < 2) {
            return response()->json([
                'data' => []
            ]);
        }

        $query = District::with('city.state')
                        ->where('name', 'like', "%{$term}%");

        if ($cityId) {
            $query->where('city_id', $cityId);
        }

        $districts = $query->orderBy('name')
                          ->limit(10)
                          ->get();

        return response()->json([
            'data' => $districts
        ]);
    }
}
