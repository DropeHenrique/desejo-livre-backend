<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimitations
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature, string $limitType = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Verificar se o usuário tem uma assinatura ativa
        if (!$user->hasActiveSubscription()) {
            return response()->json([
                'message' => 'Você precisa de uma assinatura ativa para acessar esta funcionalidade',
                'requires_subscription' => true
            ], 403);
        }

        // Verificar acesso à funcionalidade
        if (!$user->hasFeatureAccess($feature)) {
            return response()->json([
                'message' => 'Esta funcionalidade não está disponível no seu plano atual',
                'feature' => $feature,
                'upgrade_required' => true
            ], 403);
        }

        // Se especificado um tipo de limite, verificar
        if ($limitType) {
            $currentCount = $this->getCurrentCount($user, $limitType);
            $limit = $user->getFeatureLimit($limitType);

            if ($limit !== -1 && $currentCount >= $limit) {
                return response()->json([
                    'message' => 'Você atingiu o limite desta funcionalidade no seu plano atual',
                    'limit_type' => $limitType,
                    'current_count' => $currentCount,
                    'limit' => $limit,
                    'upgrade_required' => true
                ], 403);
            }
        }

        return $next($request);
    }

    /**
     * Obter contagem atual de uma funcionalidade
     */
    private function getCurrentCount($user, string $limitType): int
    {
        try {
            switch ($limitType) {
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
            \Log::warning("Erro ao contar {$limitType}: " . $e->getMessage());
            return 0;
        }
    }
}
