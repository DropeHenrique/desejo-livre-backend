<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanLimitationsController extends Controller
{
    /**
     * Obter informações do plano atual do usuário
     */
    public function getCurrentPlanInfo(Request $request): JsonResponse
    {
        $user = $request->user();
        $planInfo = $user->getPlanInfo();

        return response()->json([
            'data' => $planInfo
        ]);
    }

    /**
     * Verificar se o usuário pode acessar uma funcionalidade
     */
    public function checkFeatureAccess(Request $request): JsonResponse
    {
        $request->validate([
            'feature' => 'required|string'
        ]);

        $user = $request->user();
        $feature = $request->feature;

        $hasAccess = $user->hasFeatureAccess($feature);
        $plan = $user->getActivePlan();

        return response()->json([
            'data' => [
                'feature' => $feature,
                'has_access' => $hasAccess,
                'plan_name' => $plan?->name,
                'requires_upgrade' => !$hasAccess && !$user->hasActiveSubscription(),
                'upgrade_required' => !$hasAccess && $user->hasActiveSubscription()
            ]
        ]);
    }

    /**
     * Verificar limite de uma funcionalidade
     */
    public function checkFeatureLimit(Request $request): JsonResponse
    {
        $request->validate([
            'feature' => 'required|string'
        ]);

        $user = $request->user();
        $feature = $request->feature;

        $limit = $user->getFeatureLimit($feature);
        $currentCount = $this->getCurrentCount($user, $feature);
        $canExceed = $user->canExceedLimit($feature, $currentCount);

        return response()->json([
            'data' => [
                'feature' => $feature,
                'limit' => $limit,
                'current_count' => $currentCount,
                'can_exceed' => $canExceed,
                'remaining' => $limit === -1 ? -1 : max(0, $limit - $currentCount),
                'is_unlimited' => $limit === -1
            ]
        ]);
    }

    /**
     * Obter todas as limitações do usuário
     */
    public function getAllLimitations(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'error' => 'Usuário não autenticado'
                ], 401);
            }

            // Verificar se o usuário tem assinatura ativa
            $subscription = $user->getActiveSubscription();
            $plan = $user->getActivePlan();

            if (!$subscription || !$plan) {
                return response()->json([
                    'has_subscription' => false,
                    'message' => 'Usuário não possui assinatura ativa',
                    'plan_info' => null,
                    'limitations' => [],
                    'current_usage' => []
                ]);
            }

            // Obter informações básicas do plano
            $planInfo = [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => $plan->price,
                'duration_days' => $plan->duration_days,
                'features' => $plan->features ?? [],
                'expires_at' => $subscription->expires_at
            ];

            // Obter limitações
            $limitations = [
                'photos_limit' => $user->getFeatureLimit('photos_limit'),
                'videos_limit' => $user->getFeatureLimit('videos_limit'),
                'favorites_limit' => $user->getFeatureLimit('favorites_limit'),
                'phone_changes_limit' => $user->getFeatureLimit('phone_changes_limit'),
                'city_changes_limit' => $user->getFeatureLimit('city_changes_limit'),
                'reviews_limit' => $user->getFeatureLimit('reviews_limit'),
            ];

            // Obter uso atual (simplificado para evitar erros)
            $currentUsage = [
                'photos_count' => 0,
                'videos_count' => 0,
                'favorites_count' => $user->favorites()->count(),
                'phone_changes_count' => 0,
                'city_changes_count' => 0,
                'reviews_count' => $user->reviews()->count(),
            ];

            // Adicionar contagem de fotos e vídeos se for acompanhante
            if ($user->companionProfile) {
                $currentUsage['photos_count'] = $user->companionProfile->media()->where('type', 'photo')->count();
                $currentUsage['videos_count'] = $user->companionProfile->media()->where('type', 'video')->count();
            }

            return response()->json([
                'has_subscription' => true,
                'plan_info' => $planInfo,
                'limitations' => $limitations,
                'current_usage' => $currentUsage
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao obter limitações: ' . $e->getMessage());

            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter contagem atual de uma funcionalidade
     */
    private function getCurrentCount($user, string $feature): int
    {
        try {
            switch ($feature) {
                case 'photos_limit':
                    return $user->companionProfile?->media()->where('type', 'photo')->count() ?? 0;

                case 'videos_limit':
                    return $user->companionProfile?->media()->where('type', 'video')->count() ?? 0;

                case 'favorites_limit':
                    return $user->favorites()->count();

                case 'phone_changes_limit':
                    return $user->companionProfile?->phoneChangeHistory()->count() ?? 0;

                case 'city_changes_limit':
                    return $user->companionProfile?->cityChangeHistory()->count() ?? 0;

                case 'reviews_limit':
                    return $user->reviews()->count();

                default:
                    return 0;
            }
        } catch (\Exception $e) {
            // Log do erro para debug
            \Log::warning("Erro ao contar {$feature}: " . $e->getMessage());
            return 0;
        }
    }
}
