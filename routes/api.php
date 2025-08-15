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
use App\Http\Controllers\Api\FacialVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\PlanLimitationsController;

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

// Verificação Facial
Route::prefix('facial-verification')->group(function () {
    // Rotas públicas
    Route::post('face-login', [FacialVerificationController::class, 'faceLogin']);
    Route::post('validate-face-image', [FacialVerificationController::class, 'validateFaceImage']);

    // Rotas autenticadas
    Route::middleware(['auth.api:sanctum'])->group(function () {
        Route::post('upload', [FacialVerificationController::class, 'uploadVerification']);
        Route::get('status', [FacialVerificationController::class, 'getStatus']);

        // Rotas apenas para admin
        Route::middleware(['admin'])->group(function () {
            Route::post('approve', [FacialVerificationController::class, 'approveVerification']);
            Route::post('reject', [FacialVerificationController::class, 'rejectVerification']);
            Route::get('pending', [FacialVerificationController::class, 'listPendingVerifications']);
            Route::get('image/{verificationId}/{imageType}', [FacialVerificationController::class, 'serveVerificationImage']);
        });
    });
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
    Route::middleware(['auth.api:sanctum'])->group(function () {
        Route::post('{companion}/favorite', [FavoriteController::class, 'store']);
        Route::delete('{companion}/favorite', [FavoriteController::class, 'destroy']);
        Route::post('{companion}/review', [ReviewController::class, 'store']);
    });
});

// Perfis de travestis (públicos)
Route::prefix('transvestites')->group(function () {
    Route::get('/', [CompanionProfileController::class, 'index']);
    Route::get('featured', [CompanionProfileController::class, 'featured']);
    Route::get('city/{citySlug}', [CompanionProfileController::class, 'byCity']);
    Route::get('{companion:slug}', [CompanionProfileController::class, 'show']);

    // Rotas para favoritos e reviews (autenticadas)
    Route::middleware(['auth.api:sanctum'])->group(function () {
        Route::post('{companion}/favorite', [FavoriteController::class, 'store']);
        Route::delete('{companion}/favorite', [FavoriteController::class, 'destroy']);
        Route::post('{companion}/review', [ReviewController::class, 'store']);
    });
});

// Perfis de garotos de programa (públicos)
Route::prefix('male-escorts')->group(function () {
    Route::get('/', [CompanionProfileController::class, 'index']);
    Route::get('featured', [CompanionProfileController::class, 'featured']);
    Route::get('city/{citySlug}', [CompanionProfileController::class, 'byCity']);
    Route::get('{companion:slug}', [CompanionProfileController::class, 'show']);

    // Rotas para favoritos e reviews (autenticadas)
    Route::middleware(['auth.api:sanctum'])->group(function () {
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

Route::middleware(['auth.api:sanctum'])->group(function () {

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

    // Denúncias (usuários autenticados)
    Route::prefix('reports')->group(function () {
        Route::post('/', [\App\Http\Controllers\Api\ReportController::class, 'store']);
        Route::get('my-reports', [\App\Http\Controllers\Api\ReportController::class, 'myReports']);
    });

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

    // Limitações de Planos
    Route::prefix('plan-limitations')->group(function () {
        Route::get('current', [PlanLimitationsController::class, 'getCurrentPlanInfo']);
        Route::post('check-feature', [PlanLimitationsController::class, 'checkFeatureAccess']);
        Route::post('check-limit', [PlanLimitationsController::class, 'checkFeatureLimit']);
        Route::get('all', [PlanLimitationsController::class, 'getAllLimitations']);
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
        Route::post('/', [FavoriteController::class, 'store'])->middleware('plan.limitations:favorites_limit');
        Route::get('stats', [FavoriteController::class, 'stats']);
        Route::delete('{favorite}', [FavoriteController::class, 'destroy']);
        Route::post('toggle', [FavoriteController::class, 'toggle'])->middleware('plan.limitations:favorites_limit');
        Route::post('clear', [FavoriteController::class, 'clear']);
    });
    Route::get('companions/{companion}/is-favorite', [FavoriteController::class, 'check']);

    // Suporte
    Route::prefix('support')->group(function () {
        Route::get('tickets', [\App\Http\Controllers\Api\SupportTicketController::class, 'index']);
        Route::post('tickets', [\App\Http\Controllers\Api\SupportTicketController::class, 'store']);
        Route::post('tickets/{ticket}/close', [\App\Http\Controllers\Api\SupportTicketController::class, 'close']);
        Route::get('tickets/{ticket}/messages', [\App\Http\Controllers\Api\SupportTicketMessageController::class, 'index']);
        Route::post('tickets/{ticket}/messages', [\App\Http\Controllers\Api\SupportTicketMessageController::class, 'store']);
    });

    // Notificações
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::post('mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllRead']);
        Route::post('{notification}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markRead']);
    });

    // ============================================================================
    // ROTAS ESPECÍFICAS PARA ACOMPANHANTES
    // ============================================================================

    Route::middleware(['user.type:companion,transvestite,male_escort'])->group(function () {
        // Perfil da acompanhante
        Route::prefix('companion')->group(function () {
            Route::get('my-profile', [CompanionProfileController::class, 'myProfile']);
            Route::get('profile', [CompanionProfileController::class, 'myProfile']); // Alias para compatibilidade
            Route::put('my-profile', [CompanionProfileController::class, 'updateMyProfile']);
            Route::post('online', [CompanionProfileController::class, 'setOnline']);
            Route::post('offline', [CompanionProfileController::class, 'setOffline']);
            Route::get('stats', [CompanionProfileController::class, 'stats']);

            // Disponibilidade
            Route::get('availability', [CompanionProfileController::class, 'myAvailability']);
            Route::put('availability', [CompanionProfileController::class, 'updateAvailability']);

            // Agenda
            Route::get('bookings', [\App\Http\Controllers\Api\BookingController::class, 'companionIndex']);
        });

        // Mídia
        Route::prefix('media')->group(function () {
            Route::get('companion/{companionProfile}', [MediaController::class, 'index']);
            Route::post('companion/{companionProfile}', [MediaController::class, 'store'])->middleware('plan.limitations:photos_limit');
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
            Route::post('/', [UserController::class, 'store']);
            Route::get('{user}', [UserController::class, 'show']);
            Route::put('{user}', [UserController::class, 'update']);
            Route::delete('{user}', [UserController::class, 'destroy']);
            Route::put('{user}/toggle-status', [UserController::class, 'toggleStatus']);
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
        Route::prefix('subscriptions')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\AdminSubscriptionController::class, 'index']);
            Route::get('stats', [\App\Http\Controllers\Api\AdminSubscriptionController::class, 'stats']);
            Route::get('test', [\App\Http\Controllers\Api\AdminSubscriptionController::class, 'test']);
            Route::get('{subscription}', [\App\Http\Controllers\Api\AdminSubscriptionController::class, 'show']);
            Route::post('{subscription}/cancel', [\App\Http\Controllers\Api\AdminSubscriptionController::class, 'cancel']);
            Route::put('{subscription}/status', [\App\Http\Controllers\Api\AdminSubscriptionController::class, 'updateStatus']);
        });

        // Tickets de Suporte
        Route::prefix('tickets')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\AdminTicketController::class, 'index']);
            Route::get('stats', [\App\Http\Controllers\Api\AdminTicketController::class, 'stats']);
            Route::get('{ticket}', [\App\Http\Controllers\Api\AdminTicketController::class, 'show']);
            Route::put('{ticket}/status', [\App\Http\Controllers\Api\AdminTicketController::class, 'updateStatus']);
            Route::post('{ticket}/respond', [\App\Http\Controllers\Api\AdminTicketController::class, 'respond']);
            Route::post('upload-image', [\App\Http\Controllers\Api\AdminTicketController::class, 'uploadImage']);
        });

        // Denúncias
        Route::prefix('reports')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ReportController::class, 'index']);
            Route::get('stats', [\App\Http\Controllers\Api\ReportController::class, 'stats']);
            Route::get('{report}', [\App\Http\Controllers\Api\ReportController::class, 'show']);
            Route::put('{report}/status', [\App\Http\Controllers\Api\ReportController::class, 'updateStatus']);
        });

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

    // Chat
    Route::prefix('chat')->group(function () {
        Route::get('conversations', [ChatController::class, 'conversations']);
        Route::post('conversations/start', [ChatController::class, 'startConversation']);
        Route::get('conversations/{conversationId}/messages', [ChatController::class, 'messages']);
        Route::post('conversations/{conversationId}/messages', [ChatController::class, 'sendMessage']);
        Route::post('conversations/{conversationId}/read', [ChatController::class, 'markAsRead']);
        Route::post('conversations/{conversationId}/services', [ChatController::class, 'requestService']);
        Route::get('stats', [ChatController::class, 'stats']);
        Route::get('online-status', [ChatController::class, 'onlineStatus']);
    });
});

// Admin helper to send notification to a user
Route::middleware(['auth.api:sanctum', 'user.type:admin'])->post('/notifications/send-to-user/{user}', [\App\Http\Controllers\Api\NotificationController::class, 'sendToUser']);

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
