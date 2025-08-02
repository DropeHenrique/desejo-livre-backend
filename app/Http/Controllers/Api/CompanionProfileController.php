<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanionProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CompanionProfileController extends Controller
{
    /**
     * Listar perfis de acompanhantes
     *
     * Retorna uma lista paginada de perfis de acompanhantes verificados.
     * Suporta diversos filtros de busca e ordenação.
     *
     * @group Acompanhantes
     * @queryParam search string Buscar por nome artístico. Example: Maria
     * @queryParam city_id int Filtrar por cidade. Example: 1
     * @queryParam verified bool Filtrar por verificação (1 para verificados). Example: 1
     * @queryParam online bool Filtrar por status online. Example: 1
     * @queryParam age_min int Idade mínima. Example: 18
     * @queryParam age_max int Idade máxima. Example: 30
     * @queryParam eye_color string Cor dos olhos. Example: castanhos
     * @queryParam hair_color string Cor do cabelo. Example: loiros
     * @queryParam ethnicity string Etnia. Example: branca
     * @queryParam sort_by string Campo para ordenação (created_at, age, random). Example: age
     * @queryParam sort_order string Ordem (asc, desc). Example: asc
     * @queryParam per_page int Resultados por página (padrão: 15). Example: 10
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "artistic_name": "Maria Silva",
     *       "age": 25,
     *       "city": {
     *         "id": 1,
     *         "name": "São Paulo",
     *         "state": {
     *           "id": 1,
     *           "name": "São Paulo",
     *           "uf": "SP"
     *         }
     *       },
     *       "verified": true,
     *       "online_status": true
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 150
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $query = CompanionProfile::with(['city.state', 'user'])
                                ->where('verified', true);

        // Search filters
        if ($request->search) {
            $query->where('artistic_name', 'like', "%{$request->search}%");
        }

        if ($request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->verified) {
            $query->where('verified', $request->verified);
        }

        if ($request->online) {
            $query->where('online_status', $request->online);
        }

        if ($request->age_min) {
            $query->where('age', '>=', $request->age_min);
        }

        if ($request->age_max) {
            $query->where('age', '<=', $request->age_max);
        }

        if ($request->eye_color) {
            $query->where('eye_color', $request->eye_color);
        }

        if ($request->hair_color) {
            $query->where('hair_color', $request->hair_color);
        }

        if ($request->ethnicity) {
            $query->where('ethnicity', $request->ethnicity);
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';

        if ($sortBy === 'random') {
            $query->inRandomOrder();
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $profiles = $query->paginate($request->per_page ?? 15);

        return response()->json($profiles);
    }

    /**
     * Exibir perfil específico de acompanhante
     *
     * Retorna os detalhes completos de um perfil de acompanhante específico,
     * incluindo informações pessoais, serviços, avaliações e localização.
     *
     * @group Acompanhantes
     * @urlParam companion string required Slug do perfil da acompanhante. Example: maria-silva-sp
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "artistic_name": "Maria Silva",
     *     "slug": "maria-silva-sp",
     *     "age": 25,
     *     "about_me": "Descrição da acompanhante...",
     *     "height": 165,
     *     "weight": 55,
     *     "eye_color": "castanhos",
     *     "hair_color": "loiros",
     *     "city": {
     *       "id": 1,
     *       "name": "São Paulo",
     *       "state": {
     *         "name": "São Paulo",
     *         "uf": "SP"
     *       }
     *     },
     *     "services": [
     *       {
     *         "id": 1,
     *         "service_type": {
     *           "name": "Acompanhante"
     *         },
     *         "price": 200
     *       }
     *     ],
     *     "reviews": [
     *       {
     *         "id": 1,
     *         "rating": 5,
     *         "comment": "Excelente profissional!"
     *       }
     *     ]
     *   }
     * }
     * @response 404 {
     *   "message": "Perfil não encontrado"
     * }
     */
    public function show(string $slug): JsonResponse
    {
        $profile = CompanionProfile::with([
            'city.state',
            'user',
            'services.serviceType',
            'districts',
            'reviews' => function ($query) {
                $query->where('status', 'approved')->latest()->limit(10);
            }
        ])->where('slug', $slug)
          ->where('verified', true)
          ->firstOrFail();

        return response()->json([
            'data' => [
                'id' => $profile->id,
                'artistic_name' => $profile->artistic_name,
                'slug' => $profile->slug,
                'age' => $profile->hide_age ? null : $profile->age,
                'about_me' => $profile->about_me,
                'height' => $profile->height,
                'weight' => $profile->weight,
                'eye_color' => $profile->eye_color,
                'hair_color' => $profile->hair_color,
                'ethnicity' => $profile->ethnicity,
                'has_tattoos' => $profile->has_tattoos,
                'has_piercings' => $profile->has_piercings,
                'has_silicone' => $profile->has_silicone,
                'verified' => $profile->verified,
                'online_status' => $profile->online_status,
                'last_active' => $profile->last_active,
                'city' => $profile->city,
                'services' => $profile->services,
                'districts' => $profile->districts,
                'reviews' => $profile->reviews,
                'average_rating' => $profile->averageRating(),
                'total_reviews' => $profile->totalReviews(),
            ]
        ]);
    }

    /**
     * Get companion's own profile
     */
    public function myProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->companionProfile()->with([
            'city.state',
            'plan',
            'services.serviceType',
            'districts'
        ])->firstOrFail();

        return response()->json([
            'data' => $profile
        ]);
    }

    /**
     * Update companion's own profile
     */
    public function updateMyProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->companionProfile()->firstOrFail();

        $validator = Validator::make($request->all(), [
            'artistic_name' => 'sometimes|required|string|max:100',
            'age' => 'nullable|integer|min:18|max:100',
            'hide_age' => 'boolean',
            'about_me' => 'nullable|string|max:5000',
            'height' => 'nullable|integer|min:100|max:250',
            'weight' => 'nullable|integer|min:30|max:200',
            'eye_color' => 'nullable|string|max:50',
            'hair_color' => 'nullable|string|max:50',
            'ethnicity' => 'nullable|string|max:50',
            'has_tattoos' => 'boolean',
            'has_piercings' => 'boolean',
            'has_silicone' => 'boolean',
            'is_smoker' => 'boolean',
            'city_id' => 'nullable|exists:cities,id',
            'whatsapp' => 'nullable|string|max:20',
            'telegram' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $profile->update($request->only([
            'artistic_name', 'age', 'hide_age', 'about_me', 'height', 'weight',
            'eye_color', 'hair_color', 'ethnicity', 'has_tattoos', 'has_piercings',
            'has_silicone', 'is_smoker', 'city_id', 'whatsapp', 'telegram'
        ]));

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $profile->fresh()
        ]);
    }

    /**
     * Set companion online
     */
    public function setOnline(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->companionProfile()->firstOrFail();

        $profile->updateOnlineStatus();

        return response()->json([
            'message' => 'Status updated to online'
        ]);
    }

    /**
     * Set companion offline
     */
    public function setOffline(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->companionProfile()->firstOrFail();

        $profile->markOffline();

        return response()->json([
            'message' => 'Status updated to offline'
        ]);
    }

    /**
     * Add companion to favorites (Client only)
     */
    public function addFavorite(Request $request, CompanionProfile $companion): JsonResponse
    {
        $user = $request->user();

        // Check if already favorited
        $existing = $user->favorites()->where('companion_profile_id', $companion->id)->first();

        if ($existing) {
            return response()->json([
                'message' => 'Companion already in favorites'
            ], 409);
        }

        $user->favorites()->create([
            'companion_profile_id' => $companion->id
        ]);

        return response()->json([
            'message' => 'Added to favorites'
        ]);
    }

    /**
     * Remove companion from favorites (Client only)
     */
    public function removeFavorite(Request $request, CompanionProfile $companion): JsonResponse
    {
        $user = $request->user();

        $user->favorites()->where('companion_profile_id', $companion->id)->delete();

        return response()->json([
            'message' => 'Removed from favorites'
        ]);
    }

    /**
     * Add review for companion (Client only)
     */
    public function addReview(Request $request, CompanionProfile $companion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if user already reviewed this companion
        $existing = $companion->reviews()->where('user_id', $user->id)->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already reviewed this companion'
            ], 409);
        }

        Review::create([
            'user_id' => $user->id,
            'companion_profile_id' => $companion->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_anonymous' => $request->is_anonymous ?? false,
            'status' => 'pending' // Needs admin approval
        ]);

        return response()->json([
            'message' => 'Review submitted successfully'
        ], 201);
    }

    /**
     * Get companion statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->companionProfile()->firstOrFail();

        return response()->json([
            'profile_views' => 0, // TODO: Implement view tracking
            'total_reviews' => $profile->totalReviews(),
            'average_rating' => $profile->averageRating(),
            'total_favorites' => $profile->favorites()->count(),
            'plan_expires_at' => $profile->plan_expires_at,
            'has_active_plan' => $profile->hasActivePlan(),
        ]);
    }

    /**
     * Get client's favorites
     */
    public function favorites(Request $request): JsonResponse
    {
        $user = $request->user();

        $favorites = $user->favorites()
                         ->with('companionProfile.city.state')
                         ->paginate($request->per_page ?? 15);

        return response()->json($favorites);
    }

    /**
     * Get pending companion profiles (Admin only)
     */
    public function pending(Request $request): JsonResponse
    {
        $profiles = CompanionProfile::with(['user', 'city.state'])
                                   ->where('verified', false)
                                   ->paginate($request->per_page ?? 15);

        return response()->json($profiles);
    }

    /**
     * Verify companion profile (Admin only)
     */
    public function verify(Request $request, CompanionProfile $companion): JsonResponse
    {
        $companion->update([
            'verified' => true,
            'verification_date' => now()
        ]);

        return response()->json([
            'message' => 'Profile verified successfully'
        ]);
    }

    /**
     * Reject companion profile (Admin only)
     */
    public function reject(Request $request, CompanionProfile $companion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $companion->update([
            'verified' => false,
            'verification_date' => null
        ]);

        // TODO: Send notification to companion with rejection reason

        return response()->json([
            'message' => 'Profile rejected'
        ]);
    }
}
