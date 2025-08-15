<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminSubscriptionController extends Controller
{
    /**
     * Get all subscriptions with pagination and filters (admin only).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Subscription::with(['user', 'plan']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get paginated results
        $perPage = $request->get('per_page', 15);
        $subscriptions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Debug: verificar os dados
        \Log::info('Subscriptions data:', [
            'count' => $subscriptions->count(),
            'sample' => $subscriptions->first() ? [
                'id' => $subscriptions->first()->id,
                'plan_id' => $subscriptions->first()->plan_id,
                'plan' => $subscriptions->first()->plan ? [
                    'id' => $subscriptions->first()->plan->id,
                    'name' => $subscriptions->first()->plan->name,
                    'price' => $subscriptions->first()->plan->price,
                    'price_type' => gettype($subscriptions->first()->plan->price)
                ] : null
            ] : null
        ]);

        return response()->json([
            'data' => $subscriptions->items(),
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'per_page' => $subscriptions->perPage(),
                'total' => $subscriptions->total(),
                'last_page' => $subscriptions->lastPage(),
            ],
        ]);
    }

    /**
     * Show a specific subscription (admin only).
     */
    public function show(Subscription $subscription): JsonResponse
    {
        return response()->json([
            'data' => $subscription->load(['user', 'plan']),
        ]);
    }

    /**
     * Cancel a subscription (admin only).
     */
    public function cancel(Subscription $subscription): JsonResponse
    {
        if ($subscription->status !== 'active') {
            return response()->json([
                'message' => 'Apenas assinaturas ativas podem ser canceladas',
            ], 400);
        }

        $subscription->update([
            'status' => 'canceled',
        ]);

        return response()->json([
            'message' => 'Assinatura cancelada com sucesso',
            'data' => $subscription->load(['user', 'plan']),
        ]);
    }

    /**
     * Get subscription statistics (admin only).
     */
    public function stats(): JsonResponse
    {
        // Contagens básicas
        $totalSubscriptions = Subscription::count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $expiredSubscriptions = Subscription::where('status', 'expired')->count();
        $cancelledSubscriptions = Subscription::where('status', 'canceled')->count();

        // Receita total - usando subquery para evitar problemas de JOIN
        $totalRevenue = Subscription::where('status', 'active')
            ->whereHas('plan')
            ->get()
            ->sum(function ($subscription) {
                return $subscription->plan->price ?? 0;
            });

        // Receita mensal - usando subquery para evitar problemas de JOIN
        $monthlyRevenue = Subscription::where('status', 'active')
            ->where('created_at', '>=', now()->startOfMonth())
            ->whereHas('plan')
            ->get()
            ->sum(function ($subscription) {
                return $subscription->plan->price ?? 0;
            });

        $stats = [
            'total_subscriptions' => $totalSubscriptions,
            'active_subscriptions' => $activeSubscriptions,
            'expired_subscriptions' => $expiredSubscriptions,
            'cancelled_subscriptions' => $cancelledSubscriptions,
            'pending_subscriptions' => 0, // Não existe na constraint do banco
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'pending_payments' => 0, // Não existe na constraint do banco
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Update subscription status (admin only).
     */
    public function updateStatus(Request $request, Subscription $subscription): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:active,expired,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $subscription->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Status da assinatura atualizado com sucesso',
            'data' => $subscription->load(['user', 'plan']),
        ]);
    }

    /**
     * Test method to debug data structure (admin only).
     */
    public function test(): JsonResponse
    {
        $subscription = Subscription::with(['user', 'plan'])->first();

        if (!$subscription) {
            return response()->json(['message' => 'Nenhuma subscription encontrada']);
        }

        $debugData = [
            'subscription_id' => $subscription->id,
            'plan_id' => $subscription->plan_id,
            'plan' => $subscription->plan ? [
                'id' => $subscription->plan->id,
                'name' => $subscription->plan->name,
                'price' => $subscription->plan->price,
                'price_type' => gettype($subscription->plan->price),
                'price_raw' => $subscription->plan->getRawOriginal('price'),
                'price_attributes' => $subscription->plan->getAttributes()['price'] ?? null
            ] : null,
            'user' => $subscription->user ? [
                'id' => $subscription->user->id,
                'name' => $subscription->user->name,
                'email' => $subscription->user->email
            ] : null
        ];

        return response()->json([
            'data' => $debugData,
            'raw_subscription' => $subscription->toArray()
        ]);
    }
}
