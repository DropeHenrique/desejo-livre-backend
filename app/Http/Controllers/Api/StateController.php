<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\City;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StateController extends Controller
{
    /**
     * List all states
     */
    public function index(Request $request)
    {
        $query = State::whereHas('cities'); // Apenas estados que têm cidades

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->uf) {
            $query->where('uf', $request->uf);
        }

        $states = $query->orderBy('name')->get(); // Usar get() em vez de paginate() para lista simples

        return response()->json([
            'data' => $states,
        ]);
    }

    /**
     * Show a specific state
     */
    public function show(State $state): JsonResponse
    {
        return response()->json([
            'state' => $state
        ]);
    }

    /**
     * Get cities of a specific state
     */
    public function cities(Request $request, State $state): JsonResponse
    {
        $query = $state->cities();
        // Search by name
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $cities = $query->orderBy('name')->paginate($request->per_page ?? 50);
        return response()->json([
            'data' => $cities->items(),
            'meta' => [
                'current_page' => $cities->currentPage(),
                'per_page' => $cities->perPage(),
                'total' => $cities->total(),
                'last_page' => $cities->lastPage(),
            ],
            'links' => [
                'first' => $cities->url(1),
                'last' => $cities->url($cities->lastPage()),
                'prev' => $cities->previousPageUrl(),
                'next' => $cities->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Get districts of a specific city
     */
    public function districts(Request $request, City $city): JsonResponse
    {
        $query = $city->districts();

        // Search by name
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $districts = $query->orderBy('name')
                          ->paginate($request->per_page ?? 15);

        return response()->json($districts);
    }
}
