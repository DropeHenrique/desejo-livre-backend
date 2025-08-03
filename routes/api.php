<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CompanionProfileController;
use App\Http\Controllers\Api\StateController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\ServiceTypeController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\BlogCategoryController;
use App\Http\Controllers\Api\CepController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ============================================================================
// ROTAS PÚBLICAS
// ============================================================================

// Autenticação
Route::prefix('auth')->group(function () {
    Route::post('register/client', [AuthController::class, 'registerClient']);
    Route::post('register/companion', [AuthController::class, 'registerCompanion']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Busca de CEP (público)
Route::prefix('cep')->group(function () {
    Route::post('search', [CepController::class, 'searchByCep']);
    Route::post('search-by-address', [CepController::class, 'searchByAddress']);
    Route::post('validate', [CepController::class, 'validateCep']);
    Route::post('search-and-update', [CepController::class, 'searchAndUpdateLocation']);
});

// Dados geográficos
Route::prefix('geography')->group(function () {
    // Estados
    Route::get('states', [StateController::class, 'index']);
    Route::get('states/{state}', [StateController::class, 'show']);
    Route::get('states/{state}/cities', [StateController::class, 'cities']);

    // Cidades
    Route::get('cities', [CityController::class, 'index']);
    Route::get('cities/popular', [CityController::class, 'popular']);
    Route::get('cities/search', [CityController::class, 'search']);
    Route::get('cities/{city}', [CityController::class, 'show']);
    Route::get('cities/by-state/{state}', [CityController::class, 'byState']);

    // Bairros
    Route::get('districts', [DistrictController::class, 'index']);
    Route::get('districts/search', [DistrictController::class, 'search']);
    Route::get('districts/{district}', [DistrictController::class, 'show']);
    Route::get('districts/by-city/{city}', [DistrictController::class, 'byCity']);
});

// Estados (rota direta para compatibilidade com testes)
Route::prefix('states')->group(function () {
    Route::get('/', [StateController::class, 'index']);
    Route::get('{state}', [StateController::class, 'show']);
    Route::get('{state}/cities', [StateController::class, 'cities']);
});

// Cidades (rota direta para compatibilidade com testes)
Route::prefix('cities')->group(function () {
    Route::get('{city}/districts', [CityController::class, 'districts']);
});

// Tipos de serviço
Route::prefix('service-types')->group(function () {
    Route::get('/', [ServiceTypeController::class, 'index']);
    Route::get('popular', [ServiceTypeController::class, 'popular']);
    Route::get('{serviceType}', [ServiceTypeController::class, 'show']);
});

// Planos
Route::prefix('plans')->group(function () {
    Route::get('/', [PlanController::class, 'index']);
    Route::get('by-user-type/{userType}', [PlanController::class, 'byUserType']);
    Route::get('compare', [PlanController::class, 'compare']);
    Route::get('{plan}', [PlanController::class, 'show']);
});

// Perfis de acompanhantes (públicos)
Route::prefix('companions')->group(function () {
    Route::get('/', [CompanionProfileController::class, 'index']);
    Route::get('featured', [CompanionProfileController::class, 'featured']);
    Route::get('city/{citySlug}', [CompanionProfileController::class, 'byCity']);
    Route::get('{companion:slug}', [CompanionProfileController::class, 'show']);

    // Rotas para favoritos e reviews (autenticadas)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('{companion}/favorite', [FavoriteController::class, 'store']);
        Route::delete('{companion}/favorite', [FavoriteController::class, 'destroy']);
        Route::post('{companion}/review', [ReviewController::class, 'store']);
    });
});

// Blog (público)
Route::prefix('blog')->group(function () {
    Route::get('posts', [BlogController::class, 'index']);
    Route::get('posts/recent', [BlogController::class, 'recent']);
    Route::get('posts/featured', [BlogController::class, 'featured']);
    Route::get('posts/search', [BlogController::class, 'search']);
    Route::get('posts/{post:slug}', [BlogController::class, 'show']);

    Route::get('categories', [BlogCategoryController::class, 'index']);
    Route::get('categories/popular', [BlogCategoryController::class, 'popular']);
    Route::get('categories/{category:slug}', [BlogCategoryController::class, 'show']);
    Route::get('categories/{category:slug}/posts', [BlogCategoryController::class, 'posts']);
});

// Estatísticas (público)
Route::prefix('stats')->group(function () {
    Route::get('general', [StatsController::class, 'general']);
    Route::get('city/{citySlug}', [StatsController::class, 'city']);
});

// Busca (público)
Route::prefix('search')->group(function () {
    Route::get('cities', [SearchController::class, 'cities']);
    Route::get('companions', [SearchController::class, 'companions']);
});

// ============================================================================
// ROTAS AUTENTICADAS
// ============================================================================

Route::middleware(['auth:sanctum'])->group(function () {

    // Perfil do usuário
    Route::prefix('user')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [UserController::class, 'changePassword']);
        Route::post('deactivate', [UserController::class, 'deactivate']);
        Route::get('stats', [UserController::class, 'stats']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // Logout global
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // ============================================================================
    // ROTAS ESPECÍFICAS PARA CLIENTES
    // ============================================================================

    Route::middleware(['user.type:client'])->group(function () {
        Route::prefix('client')->group(function () {
            Route::get('profile', [AuthController::class, 'profile']);
        });
    });

    // Assinaturas
    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index']);
        Route::post('/', [SubscriptionController::class, 'store']);
        Route::get('{subscription}', [SubscriptionController::class, 'show']);
        Route::post('{subscription}/cancel', [SubscriptionController::class, 'cancel']);
        Route::post('{subscription}/renew', [SubscriptionController::class, 'renew']);
    });

    // Pagamentos
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('{payment}', [PaymentController::class, 'show']);
        Route::post('{payment}/refund', [PaymentController::class, 'refund']);
    });

    // Avaliações
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::post('/', [ReviewController::class, 'store']);
        Route::get('stats', [ReviewController::class, 'stats']);
        Route::get('{review}', [ReviewController::class, 'show']);
        Route::put('{review}', [ReviewController::class, 'update']);
        Route::delete('{review}', [ReviewController::class, 'destroy']);
    });

    // Favoritos
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/', [FavoriteController::class, 'store']);
        Route::get('stats', [FavoriteController::class, 'stats']);
        Route::delete('{favorite}', [FavoriteController::class, 'destroy']);
        Route::post('toggle', [FavoriteController::class, 'toggle']);
        Route::post('clear', [FavoriteController::class, 'clear']);
    });
    Route::get('companions/{companion}/is-favorite', [FavoriteController::class, 'check']);

    // ============================================================================
    // ROTAS ESPECÍFICAS PARA ACOMPANHANTES
    // ============================================================================

    Route::middleware(['user.type:companion'])->group(function () {
        // Perfil da acompanhante
        Route::prefix('companion')->group(function () {
            Route::get('my-profile', [CompanionProfileController::class, 'myProfile']);
            Route::get('profile', [CompanionProfileController::class, 'myProfile']); // Alias para compatibilidade
            Route::put('my-profile', [CompanionProfileController::class, 'updateMyProfile']);
            Route::post('online', [CompanionProfileController::class, 'setOnline']);
            Route::post('offline', [CompanionProfileController::class, 'setOffline']);
            Route::get('stats', [CompanionProfileController::class, 'stats']);
        });

        // Mídia
        Route::prefix('media')->group(function () {
            Route::get('companion/{companionProfile}', [MediaController::class, 'index']);
            Route::post('companion/{companionProfile}', [MediaController::class, 'store']);
            Route::get('{media}', [MediaController::class, 'show']);
            Route::put('{media}', [MediaController::class, 'update']);
            Route::delete('{media}', [MediaController::class, 'destroy']);
            Route::post('{media}/primary', [MediaController::class, 'setPrimary']);
            Route::post('companion/{companionProfile}/reorder', [MediaController::class, 'reorder']);
            Route::post('{media}/thumbnail', [MediaController::class, 'generateThumbnail']);
        });
    });

    // ============================================================================
    // ROTAS ESPECÍFICAS PARA ADMINISTRADORES
    // ============================================================================

    Route::middleware(['user.type:admin'])->prefix('admin')->group(function () {
        // Usuários
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('{user}', [UserController::class, 'show']);
            Route::put('{user}', [UserController::class, 'update']);
        });

        // Tipos de serviço
        Route::prefix('service-types')->group(function () {
            Route::post('/', [ServiceTypeController::class, 'store']);
            Route::put('{serviceType}', [ServiceTypeController::class, 'update']);
            Route::delete('{serviceType}', [ServiceTypeController::class, 'destroy']);
        });

        // Planos
        Route::prefix('plans')->group(function () {
            Route::post('/', [PlanController::class, 'store']);
            Route::put('{plan}', [PlanController::class, 'update']);
            Route::delete('{plan}', [PlanController::class, 'destroy']);
        });

        // Assinaturas
        Route::get('subscriptions/stats', [SubscriptionController::class, 'stats']);

        // Pagamentos
        Route::get('payments/stats', [PaymentController::class, 'stats']);

        // Avaliações
        Route::prefix('reviews')->group(function () {
            Route::get('pending', [ReviewController::class, 'pending']);
            Route::post('{review}/approve', [ReviewController::class, 'approve']);
            Route::post('{review}/reject', [ReviewController::class, 'reject']);
            Route::post('{review}/verify', [ReviewController::class, 'verify']);
        });

        // Perfis de acompanhantes
        Route::prefix('companions')->group(function () {
            Route::get('pending', [CompanionProfileController::class, 'pending']);
            Route::post('{companion}/verify', [CompanionProfileController::class, 'verify']);
            Route::post('{companion}/reject', [CompanionProfileController::class, 'reject']);
        });

        // Verificação de perfis (rota direta para compatibilidade com testes)
        Route::post('companions/{companion}/verify', [CompanionProfileController::class, 'verify']);

        // Blog
        Route::prefix('blog')->group(function () {
            Route::post('posts', [BlogController::class, 'store']);
            Route::put('posts/{post}', [BlogController::class, 'update']);
            Route::delete('posts/{post}', [BlogController::class, 'destroy']);
            Route::post('posts/{post}/publish', [BlogController::class, 'publish']);
            Route::post('posts/{post}/archive', [BlogController::class, 'archive']);

            Route::post('categories', [BlogCategoryController::class, 'store']);
            Route::put('categories/{category}', [BlogCategoryController::class, 'update']);
            Route::delete('categories/{category}', [BlogCategoryController::class, 'destroy']);
        });

        // Dashboard
        Route::get('dashboard', [AuthController::class, 'dashboard']);
    });

    // ============================================================================
    // ROTAS ESPECÍFICAS PARA AUTORES DO BLOG
    // ============================================================================

    Route::middleware(['can:author'])->prefix('blog')->group(function () {
        Route::post('posts', [BlogController::class, 'store']);
        Route::put('posts/{post}', [BlogController::class, 'update']);
        Route::delete('posts/{post}', [BlogController::class, 'destroy']);
        Route::post('posts/{post}/publish', [BlogController::class, 'publish']);
        Route::post('posts/{post}/archive', [BlogController::class, 'archive']);
    });
});

// ============================================================================
// WEBHOOKS (sem autenticação)
// ============================================================================

Route::prefix('webhooks')->group(function () {
    Route::post('payments', [PaymentController::class, 'processWebhook']);
});

// ============================================================================
// ROTA DE TESTE
// ============================================================================

Route::get('ping', function () {
    return response()->json(['message' => 'API is running!', 'timestamp' => now()]);
});
