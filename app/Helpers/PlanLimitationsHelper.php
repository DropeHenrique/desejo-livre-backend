<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class PlanLimitationsHelper
{
    /**
     * Verificar se o usuário pode acessar uma funcionalidade
     */
    public static function checkFeatureAccess(User $user, string $feature): bool
    {
        return $user->hasFeatureAccess($feature);
    }

    /**
     * Verificar se o usuário pode exceder um limite
     */
    public static function checkFeatureLimit(User $user, string $feature, int $currentCount): bool
    {
        return $user->canExceedLimit($feature, $currentCount);
    }

    /**
     * Resposta de erro para funcionalidade não disponível
     */
    public static function featureNotAvailableResponse(string $feature, string $planName = null): JsonResponse
    {
        return response()->json([
            'message' => 'Esta funcionalidade não está disponível no seu plano atual',
            'feature' => $feature,
            'plan_name' => $planName,
            'upgrade_required' => true
        ], 403);
    }

    /**
     * Resposta de erro para limite excedido
     */
    public static function limitExceededResponse(string $feature, int $currentCount, int $limit): JsonResponse
    {
        return response()->json([
            'message' => 'Você atingiu o limite desta funcionalidade no seu plano atual',
            'feature' => $feature,
            'current_count' => $currentCount,
            'limit' => $limit,
            'upgrade_required' => true
        ], 403);
    }

    /**
     * Resposta de erro para assinatura necessária
     */
    public static function subscriptionRequiredResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Você precisa de uma assinatura ativa para acessar esta funcionalidade',
            'requires_subscription' => true
        ], 403);
    }

    /**
     * Verificar acesso e retornar resposta de erro se necessário
     */
    public static function checkAccessOrFail(User $user, string $feature): ?JsonResponse
    {
        if (!$user->hasActiveSubscription()) {
            return self::subscriptionRequiredResponse();
        }

        if (!$user->hasFeatureAccess($feature)) {
            return self::featureNotAvailableResponse($feature, $user->getActivePlan()?->name);
        }

        return null;
    }

    /**
     * Verificar limite e retornar resposta de erro se necessário
     */
    public static function checkLimitOrFail(User $user, string $feature, int $currentCount): ?JsonResponse
    {
        if (!$user->hasActiveSubscription()) {
            return self::subscriptionRequiredResponse();
        }

        if (!$user->canExceedLimit($feature, $currentCount)) {
            $limit = $user->getFeatureLimit($feature);
            return self::limitExceededResponse($feature, $currentCount, $limit);
        }

        return null;
    }

    /**
     * Obter informações do plano para resposta
     */
    public static function getPlanInfoForResponse(User $user): array
    {
        $plan = $user->getActivePlan();

        return [
            'has_plan' => $user->hasActiveSubscription(),
            'plan_name' => $plan?->name,
            'plan_description' => $plan?->description,
            'expires_at' => $user->getActiveSubscription()?->expires_at,
            'upgrade_url' => '/planos'
        ];
    }
}
