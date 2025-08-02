<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Media;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Mídia pública pode ser vista por qualquer pessoa
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Media $media): bool
    {
        // Mídia de perfis verificados é pública
        if ($media->companionProfile->verified) {
            return true;
        }

        // Acompanhante pode ver sua própria mídia
        if ($user->isCompanion() && $user->companionProfile?->id === $media->companion_profile_id) {
            return true;
        }

        // Admin pode ver qualquer mídia
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isCompanion(); // Apenas acompanhantes podem criar mídia
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Media $media): bool
    {
        // Acompanhante pode atualizar sua própria mídia
        if ($user->isCompanion() && $user->companionProfile?->id === $media->companion_profile_id) {
            return true;
        }

        // Admin pode atualizar qualquer mídia
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Media $media): bool
    {
        // Acompanhante pode deletar sua própria mídia
        if ($user->isCompanion() && $user->companionProfile?->id === $media->companion_profile_id) {
            return true;
        }

        // Admin pode deletar qualquer mídia
        return $user->isAdmin();
    }
}
