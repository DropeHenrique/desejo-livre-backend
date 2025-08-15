<?php

namespace App\Traits;

use App\Models\Subscription;
use App\Models\Plan;

trait HasPlanLimitations
{
    /**
     * Obter a assinatura ativa do usuário
     */
    public function getActiveSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->with('plan')
            ->first();
    }

    /**
     * Verificar se o usuário tem uma assinatura ativa
     */
    public function hasActiveSubscription(): bool
    {
        return $this->getActiveSubscription() !== null;
    }

    /**
     * Obter o plano ativo do usuário
     */
    public function getActivePlan(): ?Plan
    {
        $subscription = $this->getActiveSubscription();
        return $subscription?->plan;
    }

    /**
     * Verificar se o usuário tem acesso a uma funcionalidade específica
     */
    public function hasFeatureAccess(string $feature): bool
    {
        $plan = $this->getActivePlan();

        if (!$plan) {
            return false;
        }

        $features = $plan->features ?? [];

        return in_array($feature, $features);
    }

    /**
     * Verificar limite de uma funcionalidade
     */
    public function getFeatureLimit(string $feature): int
    {
        $plan = $this->getActivePlan();

        if (!$plan) {
            return 0;
        }

        $features = $plan->features ?? [];

        // Mapeamento de funcionalidades para limites
        $limits = [
            'photos_limit' => $this->getPhotosLimit($plan),
            'videos_limit' => $this->getVideosLimit($plan),
            'favorites_limit' => $this->getFavoritesLimit($plan),
            'phone_changes_limit' => $this->getPhoneChangesLimit($plan),
            'city_changes_limit' => $this->getCityChangesLimit($plan),
            'reviews_limit' => $this->getReviewsLimit($plan),
        ];

        return $limits[$feature] ?? 0;
    }

    /**
     * Verificar se o usuário pode exceder um limite
     */
    public function canExceedLimit(string $feature, int $currentCount): bool
    {
        $limit = $this->getFeatureLimit($feature);

        if ($limit === -1) { // Ilimitado
            return true;
        }

        return $currentCount < $limit;
    }

    /**
     * Obter limite de fotos baseado no plano
     */
    private function getPhotosLimit(Plan $plan): int
    {
        $limits = [
            'Bronze' => 5,
            'Prata' => 10,
            'Ouro' => 20,
            'Black' => -1, // Ilimitado
            'Básico' => 6,
            'Premium' => -1, // Ilimitado
            'VIP' => -1, // Ilimitado
        ];

        return $limits[$plan->name] ?? 0;
    }

    /**
     * Obter limite de vídeos baseado no plano
     */
    private function getVideosLimit(Plan $plan): int
    {
        $limits = [
            'Bronze' => 0,
            'Prata' => 0,
            'Ouro' => 5,
            'Black' => -1, // Ilimitado
            'Básico' => 0,
            'Premium' => 3,
            'VIP' => -1, // Ilimitado
        ];

        return $limits[$plan->name] ?? 0;
    }

    /**
     * Obter limite de favoritos baseado no plano
     */
    private function getFavoritesLimit(Plan $plan): int
    {
        $limits = [
            'Bronze' => 10,
            'Prata' => 25,
            'Ouro' => 50,
            'Black' => -1, // Ilimitado
            'Básico' => 3,
            'Premium' => -1, // Ilimitado
            'VIP' => -1, // Ilimitado
        ];

        return $limits[$plan->name] ?? 0;
    }

    /**
     * Obter limite de trocas de telefone baseado no plano
     */
    private function getPhoneChangesLimit(Plan $plan): int
    {
        $limits = [
            'Bronze' => 1,
            'Prata' => 3,
            'Ouro' => 5,
            'Black' => -1, // Ilimitado
            'Básico' => 0,
            'Premium' => 1,
            'VIP' => -1, // Ilimitado
        ];

        return $limits[$plan->name] ?? 0;
    }

    /**
     * Obter limite de trocas de cidade baseado no plano
     */
    private function getCityChangesLimit(Plan $plan): int
    {
        $limits = [
            'Bronze' => 2,
            'Prata' => 5,
            'Ouro' => 10,
            'Black' => -1, // Ilimitado
            'Básico' => 0,
            'Premium' => 3,
            'VIP' => -1, // Ilimitado
        ];

        return $limits[$plan->name] ?? 0;
    }

    /**
     * Obter limite de avaliações baseado no plano
     */
    private function getReviewsLimit(Plan $plan): int
    {
        $limits = [
            'Bronze' => 5,
            'Prata' => 15,
            'Ouro' => 30,
            'Black' => -1, // Ilimitado
            'Básico' => 3,
            'Premium' => -1, // Ilimitado
            'VIP' => -1, // Ilimitado
        ];

        return $limits[$plan->name] ?? 0;
    }

    /**
     * Verificar se o usuário pode ver todas as fotos de um perfil
     */
    public function canViewAllPhotos(): bool
    {
        return $this->hasFeatureAccess('view_all_photos') ||
               $this->getActivePlan()?->name === 'Premium' ||
               $this->getActivePlan()?->name === 'VIP';
    }

    /**
     * Verificar se o usuário pode usar filtros avançados
     */
    public function canUseAdvancedFilters(): bool
    {
        return $this->hasFeatureAccess('advanced_filters') ||
               $this->getActivePlan()?->name === 'Premium' ||
               $this->getActivePlan()?->name === 'VIP';
    }

    /**
     * Verificar se o usuário pode fazer perguntas
     */
    public function canAskQuestions(): bool
    {
        return $this->hasFeatureAccess('ask_questions') ||
               $this->getActivePlan()?->name === 'Premium' ||
               $this->getActivePlan()?->name === 'VIP';
    }

    /**
     * Verificar se o usuário tem suporte prioritário
     */
    public function hasPrioritySupport(): bool
    {
        return $this->hasFeatureAccess('priority_support') ||
               $this->getActivePlan()?->name === 'Black' ||
               $this->getActivePlan()?->name === 'VIP';
    }

    /**
     * Verificar se o usuário pode acessar perfis VIP
     */
    public function canAccessVipProfiles(): bool
    {
        return $this->hasFeatureAccess('vip_profiles') ||
               $this->getActivePlan()?->name === 'Premium' ||
               $this->getActivePlan()?->name === 'VIP';
    }

    /**
     * Verificar se o usuário pode acessar perfis Black
     */
    public function canAccessBlackProfiles(): bool
    {
        return $this->hasFeatureAccess('black_profiles') ||
               $this->getActivePlan()?->name === 'Premium' ||
               $this->getActivePlan()?->name === 'VIP';
    }

    /**
     * Verificar se o usuário pode ocultar idade
     */
    public function canHideAge(): bool
    {
        return $this->hasFeatureAccess('hide_age') ||
               $this->getActivePlan()?->name === 'Ouro' ||
               $this->getActivePlan()?->name === 'Black';
    }

    /**
     * Verificar se o usuário pode ter destaque na busca
     */
    public function canHaveSearchHighlight(): bool
    {
        return $this->hasFeatureAccess('search_highlight') ||
               $this->getActivePlan()?->name === 'Prata' ||
               $this->getActivePlan()?->name === 'Ouro' ||
               $this->getActivePlan()?->name === 'Black';
    }

    /**
     * Obter informações do plano atual
     */
    public function getPlanInfo(): array
    {
        $plan = $this->getActivePlan();

        if (!$plan) {
            return [
                'has_plan' => false,
                'plan_name' => null,
                'expires_at' => null,
                'features' => [],
                'limits' => []
            ];
        }

        $subscription = $this->getActiveSubscription();

        return [
            'has_plan' => true,
            'plan_name' => $plan->name,
            'expires_at' => $subscription?->expires_at,
            'features' => $plan->features ?? [],
            'limits' => [
                'photos' => $this->getPhotosLimit($plan),
                'videos' => $this->getVideosLimit($plan),
                'favorites' => $this->getFavoritesLimit($plan),
                'phone_changes' => $this->getPhoneChangesLimit($plan),
                'city_changes' => $this->getCityChangesLimit($plan),
                'reviews' => $this->getReviewsLimit($plan),
            ]
        ];
    }
}
