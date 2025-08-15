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
    public function index(Request $request)
    {
        $query = CompanionProfile::with(['city.state']);

        // Filtros
        if ($request->has('state_id')) {
            $query->whereHas('city', function ($cityQuery) use ($request) {
                $cityQuery->where('state_id', $request->state_id);
            });
        }
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }
        if ($request->has('district_id')) {
            $query->whereHas('districts', function ($districtQuery) use ($request) {
                $districtQuery->where('district_id', $request->district_id);
            });
        }
        if ($request->has('verified')) {
            $query->where('verified', $request->verified);
        }
        if ($request->has('online')) {
            $query->where('online_status', $request->online);
        }
        if ($request->has('search')) {
            $query->where('artistic_name', 'like', "%{$request->search}%");
        }
        if ($request->has('age_min')) {
            $query->where('age', '>=', $request->age_min);
        }
        if ($request->has('age_max')) {
            $query->where('age', '<=', $request->age_max);
        }
        if ($request->has('eye_color')) {
            $query->where('eye_color', $request->eye_color);
        }
        if ($request->has('hair_color')) {
            $query->where('hair_color', $request->hair_color);
        }
        if ($request->has('ethnicity')) {
            $query->where('ethnicity', $request->ethnicity);
        }
        if ($request->has('user_type')) {
            $query->whereHas('user', function ($userQuery) use ($request) {
                $userQuery->where('user_type', $request->user_type);
            });
        }
        if ($request->has('height_min')) {
            $query->where('height', '>=', $request->height_min);
        }
        if ($request->has('height_max')) {
            $query->where('height', '<=', $request->height_max);
        }
        if ($request->has('weight_min')) {
            $query->where('weight', '>=', $request->weight_min);
        }
        if ($request->has('weight_max')) {
            $query->where('weight', '<=', $request->weight_max);
        }

        // Ordenação
        $sortBy = $request->get('sort_by', 'newest');
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'rating':
                $query->orderBy('average_rating', 'desc');
                break;
            case 'price_low':
                $query->with(['services' => function ($serviceQuery) {
                    $serviceQuery->orderBy('price', 'asc');
                }]);
                break;
            case 'price_high':
                $query->with(['services' => function ($serviceQuery) {
                    $serviceQuery->orderBy('price', 'desc');
                }]);
                break;
            default:
                $query->orderBy('id', 'desc');
        }

        $companions = $query->paginate($request->per_page ?? 15);
        return response()->json([
            'data' => $companions->items(),
            'meta' => [
                'current_page' => $companions->currentPage(),
                'per_page' => $companions->perPage(),
                'total' => $companions->total(),
                'last_page' => $companions->lastPage(),
            ],
            'links' => [
                'first' => $companions->url(1),
                'last' => $companions->url($companions->lastPage()),
                'prev' => $companions->previousPageUrl(),
                'next' => $companions->nextPageUrl(),
            ]
        ]);
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
            'media', // Adicionando relacionamento com mídia
            'reviews' => function ($query) {
                $query->where('status', 'approved')->latest()->limit(10);
            }
        ])->where('slug', $slug)
          ->where('verified', true)
          ->firstOrFail();

        return response()->json([
            'data' => [
                'id' => $profile->id,
                'user_id' => $profile->user_id,
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
                'media' => $profile->media, // Incluindo dados de mídia
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
            // Deslocamento
            'attends_home' => 'nullable|boolean',
            'travel_radius_km' => 'nullable|integer|min:0',
            // Bairros atendidos
            'district_ids' => 'nullable|array',
            'district_ids.*' => 'integer|exists:districts,id',
            // Serviços
            'services' => 'nullable|array',
            'services.*.service_type_id' => 'required_with:services|integer|exists:service_types,id',
            'services.*.price' => 'nullable|numeric|min:0',
            'services.*.unit_minutes' => 'nullable|integer|in:30,45,60,90,120',
            'services.*.description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Normalizar aliases
        $attendsHome = $request->boolean('attends_home')
            || $request->boolean('home_service')
            || ($request->has('domicilio') ? filter_var($request->input('domicilio'), FILTER_VALIDATE_BOOLEAN) : false)
            || ($request->has('attendsHome') ? filter_var($request->input('attendsHome'), FILTER_VALIDATE_BOOLEAN) : false);

        $travelRadius = $request->input('travel_radius_km', $request->input('travel_radius', $request->input('radius_km', $request->input('service_radius_km'))));
        $travelRadius = ($travelRadius === '' || $travelRadius === null) ? null : (int) $travelRadius;

        $updateData = $request->only([
            'artistic_name', 'age', 'hide_age', 'about_me', 'height', 'weight',
            'eye_color', 'hair_color', 'ethnicity', 'has_tattoos', 'has_piercings',
            'has_silicone', 'is_smoker', 'city_id', 'whatsapp', 'telegram'
        ]);
        // Adicionar deslocamento se presentes
        if ($request->hasAny(['attends_home', 'home_service', 'domicilio', 'attendsHome'])) {
            $updateData['attends_home'] = $attendsHome;
        }
        if ($request->hasAny(['travel_radius_km', 'travel_radius', 'radius_km', 'service_radius_km'])) {
            $updateData['travel_radius_km'] = $travelRadius;
        }

        $profile->update($updateData);

        // Sincronizar serviços, se fornecidos
        if ($request->has('services')) {
            $services = $request->input('services', []);

            // Mapear por service_type_id para facilitar upsert
            $serviceTypeIds = [];
            foreach ($services as $service) {
                $serviceTypeId = (int) $service['service_type_id'];
                $serviceTypeIds[] = $serviceTypeId;
                $profile->services()->updateOrCreate(
                    ['service_type_id' => $serviceTypeId],
                    [
                        'price' => $service['price'] ?? null,
                        'unit_minutes' => $service['unit_minutes'] ?? 60,
                        'description' => $service['description'] ?? null,
                    ]
                );
            }

            // Remover serviços que não foram enviados agora
            if (count($serviceTypeIds) > 0) {
                $profile->services()
                    ->whereNotIn('service_type_id', $serviceTypeIds)
                    ->delete();
            } else {
                // Caso lista vazia, remover todos
                $profile->services()->delete();
            }
        }

        // Sincronizar bairros atendidos, se enviados
        if ($request->hasAny(['district_ids', 'districts'])) {
            $districtIds = $request->input('district_ids');
            if (!$districtIds && is_array($request->input('districts'))) {
                $districtsPayload = $request->input('districts');
                // Pode vir como array de ids ou objetos
                $districtIds = array_map(function ($d) {
                    return is_array($d) && isset($d['id']) ? (int) $d['id'] : (int) $d;
                }, $districtsPayload);
            }
            $districtIds = array_values(array_filter(array_map('intval', (array) $districtIds)));
            $profile->districts()->sync($districtIds);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $profile->fresh()->load(['services.serviceType', 'districts'])
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
        $companionProfileId = $request->input('companion_profile_id', $companion->id);

        // Check if already favorited
        $existing = $user->favorites()->where('companion_profile_id', $companionProfileId)->first();

        if ($existing) {
            return response()->json([
                'message' => 'Companion already in favorites'
            ], 409);
        }

        $user->favorites()->create([
            'companion_profile_id' => $companionProfileId
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
        $companionProfileId = $request->input('companion_profile_id', $companion->id);

        $user->favorites()->where('companion_profile_id', $companionProfileId)->delete();

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
        $companionProfileId = $request->input('companion_profile_id', $companion->id);

        // Check if user already reviewed this companion
        $existing = $companion->reviews()->where('user_id', $user->id)->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already reviewed this companion'
            ], 409);
        }

        Review::create([
            'user_id' => $user->id,
            'companion_profile_id' => $companionProfileId,
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

    /**
     * Listar acompanhantes em destaque
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 6);

            $companions = CompanionProfile::with(['city.state', 'media', 'reviews'])
                ->where('verified', true)
                ->where('online_status', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => $companions,
                'message' => 'Acompanhantes em destaque carregadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao carregar acompanhantes em destaque',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar acompanhantes por cidade
     */
    public function byCity(Request $request, string $citySlug): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 12);
            $sort = $request->get('sort', 'created_at');

            $city = \App\Models\City::where('slug', $citySlug)->first();

            if (!$city) {
                return response()->json([
                    'message' => 'Cidade não encontrada'
                ], 404);
            }

            $query = CompanionProfile::with(['city.state', 'media', 'reviews'])
                ->where('city_id', $city->id)
                ->where('verified', true);

            // Aplicar ordenação
            switch ($sort) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'rating':
                    $query->orderBy('average_rating', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            $companions = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'data' => $companions->items(),
                'current_page' => $companions->currentPage(),
                'last_page' => $companions->lastPage(),
                'per_page' => $companions->perPage(),
                'total' => $companions->total(),
                'from' => $companions->firstItem(),
                'to' => $companions->lastItem(),
                'message' => 'Acompanhantes da cidade carregadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao carregar acompanhantes da cidade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibir perfil de uma acompanhante específica
     *
     * @group Acompanhantes
     * @urlParam slug string required Slug da acompanhante. Example: maria-silva
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "artistic_name": "Maria Silva",
     *     "slug": "maria-silva",
     *     "age": 25,
     *     "about_me": "Descrição da acompanhante...",
     *     "verified": true,
     *     "online_status": true,
     *     "city": {
     *       "id": 1,
     *       "name": "São Paulo",
     *       "state": {
     *         "id": 1,
     *         "name": "São Paulo",
     *         "uf": "SP"
     *       }
     *     },
     *     "media": [
     *       {
     *         "id": 1,
     *         "file_path": "photos/maria-silva-1.jpg",
     *         "file_type": "photo",
     *         "is_primary": true
     *       }
     *     ],
     *     "reviews": [
     *       {
     *         "id": 1,
     *         "rating": 5,
     *         "comment": "Excelente atendimento!",
     *         "user": {
     *           "name": "João Silva"
     *         }
     *       }
     *     ]
     *   }
     * }
     */

    /**
     * Get companion weekly availability
     */
    public function myAvailability(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->companionProfile()->firstOrFail();

        $availabilities = $profile->availabilities()
            ->orderBy('day_of_week')
            ->get();

        return response()->json([
            'data' => $availabilities,
        ]);
    }

    /**
     * Update companion weekly availability
     */
    public function updateAvailability(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->companionProfile()->firstOrFail();

        $validator = Validator::make($request->all(), [
            'availability' => 'required|array',
            'availability.*.day_of_week' => 'required|integer|min:0|max:6',
            'availability.*.start_time' => 'required|date_format:H:i',
            'availability.*.end_time' => 'required|date_format:H:i|after:availability.*.start_time',
            'availability.*.enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $availability = collect($request->input('availability', []))
            ->map(function ($slot) {
                return [
                    'day_of_week' => (int) $slot['day_of_week'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'enabled' => (bool) ($slot['enabled'] ?? true),
                ];
            })
            ->filter(fn ($slot) => $slot['enabled'] === true)
            ->values();

        \DB::transaction(function () use ($profile, $availability) {
            $profile->availabilities()->delete();
            foreach ($availability as $slot) {
                $profile->availabilities()->create($slot);
            }
        });

        return response()->json([
            'message' => 'Availability updated successfully',
            'data' => $profile->availabilities()->orderBy('day_of_week')->get(),
        ]);
    }
}
