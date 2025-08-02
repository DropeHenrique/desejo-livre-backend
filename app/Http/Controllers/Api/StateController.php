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
    public function index(Request $request): JsonResponse
    {
        $query = State::query();

        // Search by name or UF
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('uf', 'like', "%{$search}%");
            });
        }

        // Filter by UF
        if ($request->uf) {
            $query->where('uf', strtoupper($request->uf));
        }

        $states = $query->orderBy('name')
                       ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $states->items(),
            'meta' => [
                'current_page' => $states->currentPage(),
                'per_page' => $states->perPage(),
                'total' => $states->total(),
                'last_page' => $states->lastPage(),
            ]
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

        $cities = $query->orderBy('name')
                       ->paginate($request->per_page ?? 50);

        return response()->json($cities);
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
