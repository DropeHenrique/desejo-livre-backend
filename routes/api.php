<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanionProfileController;
use App\Http\Controllers\Api\StateController;
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

// Rotas públicas de autenticação
Route::prefix('auth')->group(function () {
    Route::post('register/client', [AuthController::class, 'registerClient']);
    Route::post('register/companion', [AuthController::class, 'registerCompanion']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Rotas públicas de consulta
Route::get('states', [StateController::class, 'index']);
Route::get('states/{state}/cities', [StateController::class, 'cities']);
Route::get('cities/{city}/districts', [StateController::class, 'districts']);

// Rotas públicas de perfis (apenas leitura)
Route::get('companions', [CompanionProfileController::class, 'index']);
Route::get('companions/{companion}', [CompanionProfileController::class, 'show']);

// Rotas protegidas para clientes
Route::middleware(['auth:client'])->prefix('client')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Favoritos
    Route::get('favorites', [CompanionProfileController::class, 'favorites']);
    Route::post('companions/{companion}/favorite', [CompanionProfileController::class, 'addFavorite']);
    Route::delete('companions/{companion}/favorite', [CompanionProfileController::class, 'removeFavorite']);

    // Avaliações
    Route::post('companions/{companion}/review', [CompanionProfileController::class, 'addReview']);
});

// Rotas protegidas para acompanhantes
Route::middleware(['auth:companion'])->prefix('companion')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Gerenciamento do perfil
    Route::get('my-profile', [CompanionProfileController::class, 'myProfile']);
    Route::put('my-profile', [CompanionProfileController::class, 'updateMyProfile']);

    // Mídia
    Route::post('my-profile/photos', [CompanionProfileController::class, 'uploadPhoto']);
    Route::delete('photos/{media}', [CompanionProfileController::class, 'deletePhoto']);
    Route::post('my-profile/videos', [CompanionProfileController::class, 'uploadVideo']);
    Route::delete('videos/{media}', [CompanionProfileController::class, 'deleteVideo']);

    // Status online
    Route::post('online', [CompanionProfileController::class, 'setOnline']);
    Route::post('offline', [CompanionProfileController::class, 'setOffline']);

    // Estatísticas
    Route::get('stats', [CompanionProfileController::class, 'stats']);
});

// Rotas protegidas para administradores
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Moderação de perfis
    Route::get('companions/pending', [CompanionProfileController::class, 'pending']);
    Route::post('companions/{companion}/verify', [CompanionProfileController::class, 'verify']);
    Route::post('companions/{companion}/reject', [CompanionProfileController::class, 'reject']);

    // Gestão de usuários
    Route::get('users', [AuthController::class, 'listUsers']);
    Route::put('users/{user}/toggle-status', [AuthController::class, 'toggleUserStatus']);

    // Dashboard
    Route::get('dashboard', [AuthController::class, 'dashboard']);
});

// Rotas autenticadas gerais (qualquer tipo de usuário)
Route::middleware(['auth:api'])->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });
});
