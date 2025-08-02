<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CompanionProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanionProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Qualquer pessoa pode ver a lista de perfis
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CompanionProfile $companionProfile): bool
    {
        // Perfis verificados são públicos
        if ($companionProfile->verified) {
            return true;
        }

        // Acompanhante pode ver seu próprio perfil
        if ($user->isCompanion() && $user->companionProfile?->id === $companionProfile->id) {
            return true;
        }

        // Admin pode ver qualquer perfil
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isCompanion() && !$user->companionProfile;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompanionProfile $companionProfile): bool
    {
        // Acompanhante pode atualizar seu próprio perfil
        if ($user->isCompanion() && $user->companionProfile?->id === $companionProfile->id) {
            return true;
        }

        // Admin pode atualizar qualquer perfil
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompanionProfile $companionProfile): bool
    {
        // Acompanhante pode deletar seu próprio perfil
        if ($user->isCompanion() && $user->companionProfile?->id === $companionProfile->id) {
            return true;
        }

        // Admin pode deletar qualquer perfil
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can verify the model.
     */
    public function verify(User $user, CompanionProfile $companionProfile): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, CompanionProfile $companionProfile): bool
    {
        return $user->isAdmin();
    }
}
