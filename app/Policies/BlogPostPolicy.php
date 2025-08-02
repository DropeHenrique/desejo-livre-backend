<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BlogPost;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlogPostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Posts publicados são públicos
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BlogPost $post): bool
    {
        // Posts publicados são públicos
        if ($post->isPublished()) {
            return true;
        }

        // Autor pode ver seu próprio post
        if ($user->id === $post->user_id) {
            return true;
        }

        // Admin pode ver qualquer post
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isAuthor();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BlogPost $post): bool
    {
        // Autor pode atualizar seu próprio post
        if ($user->id === $post->user_id) {
            return true;
        }

        // Admin pode atualizar qualquer post
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BlogPost $post): bool
    {
        // Autor pode deletar seu próprio post
        if ($user->id === $post->user_id) {
            return true;
        }

        // Admin pode deletar qualquer post
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, BlogPost $post): bool
    {
        // Autor pode publicar seu próprio post
        if ($user->id === $post->user_id) {
            return true;
        }

        // Admin pode publicar qualquer post
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can archive the model.
     */
    public function archive(User $user, BlogPost $post): bool
    {
        // Autor pode arquivar seu próprio post
        if ($user->id === $post->user_id) {
            return true;
        }

        // Admin pode arquivar qualquer post
        return $user->isAdmin();
    }
}
