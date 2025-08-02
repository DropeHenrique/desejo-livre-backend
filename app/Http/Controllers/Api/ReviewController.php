<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\CompanionProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Listar avaliações
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'companionProfile']);

        if ($request->companion_profile_id) {
            $query->where('companion_profile_id', $request->companion_profile_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->rating) {
            $query->where('rating', $request->rating);
        }

        if ($request->verified) {
            $query->where('is_verified', $request->verified);
        }

        $reviews = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'last_page' => $reviews->lastPage(),
            ]
        ]);
    }

    /**
     * Mostrar avaliação específica
     */
    public function show(Review $review): JsonResponse
    {
        $review->load(['user', 'companionProfile']);

        return response()->json([
            'data' => $review
        ]);
    }

    /**
     * Criar nova avaliação
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'companion_profile_id' => 'required|exists:companion_profiles,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $companionProfile = CompanionProfile::findOrFail($request->companion_profile_id);

        // Verificar se o usuário já avaliou esta acompanhante
        $existingReview = Review::where('user_id', $user->id)
                               ->where('companion_profile_id', $companionProfile->id)
                               ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'Você já avaliou esta acompanhante'
            ], 422);
        }

        // Verificar se o usuário não está avaliando a si mesmo
        if ($user->isCompanion() && $user->companionProfile && $user->companionProfile->id === $companionProfile->id) {
            return response()->json([
                'message' => 'Você não pode se auto-avaliar'
            ], 422);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'companion_profile_id' => $companionProfile->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_anonymous' => $request->is_anonymous ?? false,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Avaliação criada com sucesso',
            'data' => $review->load(['user', 'companionProfile'])
        ], 201);
    }

    /**
     * Atualizar avaliação
     */
    public function update(Request $request, Review $review): JsonResponse
    {
        $this->authorize('update', $review);

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update($request->only(['rating', 'comment', 'is_anonymous']));

        return response()->json([
            'message' => 'Avaliação atualizada com sucesso',
            'data' => $review->fresh()->load(['user', 'companionProfile'])
        ]);
    }

    /**
     * Excluir avaliação
     */
    public function destroy(Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return response()->json([
            'message' => 'Avaliação excluída com sucesso'
        ]);
    }

    /**
     * Aprovar avaliação (Admin)
     */
    public function approve(Review $review): JsonResponse
    {
        $this->authorize('approve', $review);

        $review->approve();

        return response()->json([
            'message' => 'Avaliação aprovada com sucesso'
        ]);
    }

    /**
     * Rejeitar avaliação (Admin)
     */
    public function reject(Review $review): JsonResponse
    {
        $this->authorize('approve', $review);

        $review->reject();

        return response()->json([
            'message' => 'Avaliação rejeitada com sucesso'
        ]);
    }

    /**
     * Marcar avaliação como verificada (Admin)
     */
    public function verify(Review $review): JsonResponse
    {
        $this->authorize('approve', $review);

        $review->markAsVerified();

        return response()->json([
            'message' => 'Avaliação marcada como verificada'
        ]);
    }

    /**
     * Avaliações pendentes (Admin)
     */
    public function pending(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Review::class);

        $reviews = Review::with(['user', 'companionProfile'])
                        ->pending()
                        ->latest()
                        ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'last_page' => $reviews->lastPage(),
            ]
        ]);
    }

    /**
     * Estatísticas de avaliações
     */
    public function stats(Request $request): JsonResponse
    {
        $companionProfileId = $request->get('companion_profile_id');

        $query = Review::query();

        if ($companionProfileId) {
            $query->where('companion_profile_id', $companionProfileId);
        }

        $stats = [
            'total_reviews' => $query->count(),
            'approved_reviews' => (clone $query)->approved()->count(),
            'pending_reviews' => (clone $query)->pending()->count(),
            'rejected_reviews' => (clone $query)->rejected()->count(),
            'verified_reviews' => (clone $query)->verified()->count(),
            'average_rating' => (clone $query)->approved()->avg('rating') ?? 0,
            'rating_distribution' => (clone $query)->approved()
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->orderBy('rating')
                ->get(),
        ];

        return response()->json([
            'data' => $stats
        ]);
    }
}
