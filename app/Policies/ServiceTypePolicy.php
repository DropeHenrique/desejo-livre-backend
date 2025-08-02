<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServiceType;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceTypePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tipos de serviço são públicos
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceType $serviceType): bool
    {
        return true; // Tipos de serviço são públicos
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceType $serviceType): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceType $serviceType): bool
    {
        return $user->isAdmin();
    }
}
