<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\CompanionProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    /**
     * Listar favoritos do usuário
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $favorites = $user->favorites()
                         ->with(['companionProfile.city.state', 'companionProfile.primaryPhoto'])
                         ->latest()
                         ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $favorites->items(),
            'meta' => [
                'current_page' => $favorites->currentPage(),
                'per_page' => $favorites->perPage(),
                'total' => $favorites->total(),
                'last_page' => $favorites->lastPage(),
            ]
        ]);
    }

    /**
     * Adicionar aos favoritos
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'companion_profile_id' => 'required|exists:companion_profiles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $companionProfile = CompanionProfile::findOrFail($request->companion_profile_id);

        // Verificar se já está nos favoritos
        $existingFavorite = Favorite::where('user_id', $user->id)
                                   ->where('companion_profile_id', $companionProfile->id)
                                   ->first();

        if ($existingFavorite) {
            return response()->json([
                'message' => 'Esta acompanhante já está nos seus favoritos'
            ], 422);
        }

        // Verificar se o usuário não está favoritando a si mesmo
        if ($user->isCompanion() && $user->companionProfile && $user->companionProfile->id === $companionProfile->id) {
            return response()->json([
                'message' => 'Você não pode se adicionar aos favoritos'
            ], 422);
        }

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'companion_profile_id' => $companionProfile->id,
        ]);

        return response()->json([
            'message' => 'Adicionado aos favoritos com sucesso',
            'data' => $favorite->load(['companionProfile.city.state'])
        ], 201);
    }

    /**
     * Remover dos favoritos
     */
    public function destroy(Favorite $favorite): JsonResponse
    {
        $this->authorize('delete', $favorite);

        $favorite->delete();

        return response()->json([
            'message' => 'Removido dos favoritos com sucesso'
        ]);
    }

    /**
     * Verificar se está nos favoritos
     */
    public function check(Request $request, CompanionProfile $companionProfile): JsonResponse
    {
        $user = $request->user();

        $isFavorite = Favorite::where('user_id', $user->id)
                              ->where('companion_profile_id', $companionProfile->id)
                              ->exists();

        return response()->json([
            'data' => [
                'is_favorite' => $isFavorite
            ]
        ]);
    }

    /**
     * Toggle favorito (adicionar/remover)
     */
    public function toggle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'companion_profile_id' => 'required|exists:companion_profiles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $companionProfile = CompanionProfile::findOrFail($request->companion_profile_id);

        // Verificar se o usuário não está favoritando a si mesmo
        if ($user->isCompanion() && $user->companionProfile && $user->companionProfile->id === $companionProfile->id) {
            return response()->json([
                'message' => 'Você não pode se adicionar aos favoritos'
            ], 422);
        }

        $favorite = Favorite::where('user_id', $user->id)
                           ->where('companion_profile_id', $companionProfile->id)
                           ->first();

        if ($favorite) {
            $favorite->delete();
            $message = 'Removido dos favoritos';
            $isFavorite = false;
        } else {
            $favorite = Favorite::create([
                'user_id' => $user->id,
                'companion_profile_id' => $companionProfile->id,
            ]);
            $message = 'Adicionado aos favoritos';
            $isFavorite = true;
        }

        return response()->json([
            'message' => $message,
            'data' => [
                'is_favorite' => $isFavorite,
                'favorite' => $favorite
            ]
        ]);
    }

    /**
     * Limpar todos os favoritos
     */
    public function clear(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->favorites()->delete();

        return response()->json([
            'message' => 'Todos os favoritos foram removidos'
        ]);
    }

    /**
     * Estatísticas de favoritos
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total_favorites' => $user->favorites()->count(),
            'favorites_this_month' => $user->favorites()
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
        ];

        return response()->json([
            'data' => $stats
        ]);
    }
}
