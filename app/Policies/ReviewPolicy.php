<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Review;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Qualquer pessoa pode ver avaliações aprovadas
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Review $review): bool
    {
        // Avaliações aprovadas são públicas
        if ($review->isApproved()) {
            return true;
        }

        // Usuário pode ver sua própria avaliação
        if ($user->id === $review->user_id) {
            return true;
        }

        // Admin pode ver qualquer avaliação
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isClient(); // Apenas clientes podem criar avaliações
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Review $review): bool
    {
        // Usuário pode atualizar sua própria avaliação
        if ($user->id === $review->user_id) {
            return true;
        }

        // Admin pode atualizar qualquer avaliação
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Review $review): bool
    {
        // Usuário pode deletar sua própria avaliação
        if ($user->id === $review->user_id) {
            return true;
        }

        // Admin pode deletar qualquer avaliação
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can verify the model.
     */
    public function verify(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }
}
