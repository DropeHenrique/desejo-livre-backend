<?php

namespace App\Providers;

use App\Models\User;
use App\Models\CompanionProfile;
use App\Models\Review;
use App\Models\Favorite;
use App\Models\Media;
use App\Models\BlogPost;
use App\Models\ServiceType;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Policies\UserPolicy;
use App\Policies\CompanionProfilePolicy;
use App\Policies\ReviewPolicy;
use App\Policies\FavoritePolicy;
use App\Policies\MediaPolicy;
use App\Policies\BlogPostPolicy;
use App\Policies\ServiceTypePolicy;
use App\Policies\PlanPolicy;
use App\Policies\SubscriptionPolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        CompanionProfile::class => CompanionProfilePolicy::class,
        Review::class => ReviewPolicy::class,
        Favorite::class => FavoritePolicy::class,
        Media::class => MediaPolicy::class,
        BlogPost::class => BlogPostPolicy::class,
        ServiceType::class => ServiceTypePolicy::class,
        Plan::class => PlanPolicy::class,
        Subscription::class => SubscriptionPolicy::class,
        Payment::class => PaymentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates para verificar tipos de usuário
        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('companion', function (User $user) {
            return $user->isCompanion();
        });

        Gate::define('client', function (User $user) {
            return $user->isClient();
        });

        Gate::define('author', function (User $user) {
            return $user->isAdmin() || $user->isAuthor();
        });
    }
}
