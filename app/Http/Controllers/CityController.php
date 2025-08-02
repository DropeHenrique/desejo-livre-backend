<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = City::with('state');

        // Filtro por estado
        if ($request->has('state_id')) {
            $query->byState($request->state_id);
        }

        // Busca por nome
        if ($request->has('search')) {
            $query->where('name', 'ILIKE', '%' . $request->search . '%');
        }

        $cities = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $city = City::with(['state', 'districts'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $city
        ]);
    }

    /**
     * Get districts by city
     */
    public function districts(string $id): JsonResponse
    {
        $city = City::findOrFail($id);
        $districts = $city->districts()->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $districts
        ]);
    }

    /**
     * Get companion profiles by city
     */
    public function companions(string $id): JsonResponse
    {
        $city = City::findOrFail($id);
        $companions = $city->companionProfiles()
            ->with(['user', 'plan'])
            ->where('verified', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $companions
        ]);
    }

    /**
     * Search cities by state UF
     */
    public function byStateUf(string $uf): JsonResponse
    {
        $state = State::byUf($uf)->firstOrFail();
        $cities = $state->cities()->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }
}
