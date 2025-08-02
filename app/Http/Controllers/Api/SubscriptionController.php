<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /**
     * Listar assinaturas do usuário
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->subscriptions()->with(['plan']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $subscriptions->items(),
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'per_page' => $subscriptions->perPage(),
                'total' => $subscriptions->total(),
                'last_page' => $subscriptions->lastPage(),
            ]
        ]);
    }

    /**
     * Mostrar assinatura específica
     */
    public function show(Subscription $subscription): JsonResponse
    {
        $this->authorize('view', $subscription);

        $subscription->load(['plan', 'payments']);

        return response()->json([
            'data' => $subscription
        ]);
    }

    /**
     * Criar nova assinatura
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $plan = Plan::findOrFail($request->plan_id);

        // Verificar se o usuário já tem uma assinatura ativa
        $activeSubscription = $user->subscriptions()->active()->first();
        if ($activeSubscription) {
            return response()->json([
                'message' => 'Você já possui uma assinatura ativa'
            ], 422);
        }

        // Verificar se o plano é compatível com o tipo de usuário
        if ($plan->user_type !== $user->user_type) {
            return response()->json([
                'message' => 'Este plano não é compatível com seu tipo de usuário'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days),
            ]);

            // Se for acompanhante, atualizar o perfil
            if ($user->isCompanion() && $user->companionProfile) {
                $user->companionProfile->update([
                    'plan_id' => $plan->id,
                    'plan_expires_at' => $subscription->expires_at,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Assinatura criada com sucesso',
                'data' => $subscription->load('plan')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar assinatura'
            ], 500);
        }
    }

    /**
     * Cancelar assinatura
     */
    public function cancel(Subscription $subscription): JsonResponse
    {
        $this->authorize('update', $subscription);

        if (!$subscription->isActive()) {
            return response()->json([
                'message' => 'Esta assinatura não está ativa'
            ], 422);
        }

        $subscription->cancel();

        // Se for acompanhante, remover plano do perfil
        if ($subscription->user->isCompanion() && $subscription->user->companionProfile) {
            $subscription->user->companionProfile->update([
                'plan_id' => null,
                'plan_expires_at' => null,
            ]);
        }

        return response()->json([
            'message' => 'Assinatura cancelada com sucesso'
        ]);
    }

    /**
     * Renovar assinatura
     */
    public function renew(Subscription $subscription): JsonResponse
    {
        $this->authorize('update', $subscription);

        if ($subscription->isActive()) {
            return response()->json([
                'message' => 'Esta assinatura ainda está ativa'
            ], 422);
        }

        $plan = $subscription->plan;

        $subscription->update([
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addDays($plan->duration_days),
        ]);

        // Se for acompanhante, atualizar o perfil
        if ($subscription->user->isCompanion() && $subscription->user->companionProfile) {
            $subscription->user->companionProfile->update([
                'plan_id' => $plan->id,
                'plan_expires_at' => $subscription->expires_at,
            ]);
        }

        return response()->json([
            'message' => 'Assinatura renovada com sucesso',
            'data' => $subscription->fresh()->load('plan')
        ]);
    }

    /**
     * Estatísticas de assinaturas (Admin)
     */
    public function stats(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Subscription::class);

        $stats = [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::active()->count(),
            'expired_subscriptions' => Subscription::expired()->count(),
            'canceled_subscriptions' => Subscription::canceled()->count(),
            'expiring_soon' => Subscription::expiresSoon()->count(),
            'revenue_this_month' => Subscription::active()
                ->where('starts_at', '>=', now()->startOfMonth())
                ->with('plan')
                ->get()
                ->sum(function ($subscription) {
                    return $subscription->plan->price;
                }),
        ];

        return response()->json([
            'data' => $stats
        ]);
    }
}
