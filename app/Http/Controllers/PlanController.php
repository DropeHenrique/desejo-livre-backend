<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Plan::active();

        // Filtro por tipo de usuário
        if ($request->has('user_type')) {
            if ($request->user_type === 'companion') {
                $query->forCompanions();
            } elseif ($request->user_type === 'client') {
                $query->forClients();
            }
        }

        $plans = $query->orderBy('price')->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $plan = Plan::active()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }

    /**
     * Get plans for companions
     */
    public function forCompanions(): JsonResponse
    {
        $plans = Plan::active()
            ->forCompanions()
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Get plans for clients
     */
    public function forClients(): JsonResponse
    {
        $plans = Plan::active()
            ->forClients()
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Get plan by slug
     */
    public function bySlug(string $slug): JsonResponse
    {
        $plan = Plan::active()->where('slug', $slug)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }
}
