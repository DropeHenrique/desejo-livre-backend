<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Listar usuários (Admin)
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['companionProfile']);

        if ($request->user_type) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->active !== null) {
            $query->where('active', $request->active);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ]
        ]);
    }

    /**
     * Mostrar usuário específico
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $user->load(['companionProfile', 'subscriptions', 'payments']);

        return response()->json([
            'data' => $user
        ]);
    }

    /**
     * Atualizar usuário
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'active' => 'sometimes|boolean',
            'user_type' => 'sometimes|in:client,companion,transvestite,male_escort,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'phone', 'active', 'user_type']));

        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'data' => $user->fresh()
        ]);
    }

    /**
     * Criar novo usuário (Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:client,companion,transvestite,male_escort,admin',
            'active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'active' => $request->active ?? true,
        ]);

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'data' => $user
        ], 201);
    }

    /**
     * Excluir usuário (Admin)
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json([
            'message' => 'Usuário excluído com sucesso'
        ]);
    }

    /**
     * Alterar status do usuário (Admin)
     */
    public function toggleStatus(User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->update(['active' => !$user->active]);

        return response()->json([
            'message' => 'Status do usuário atualizado com sucesso',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Alterar senha
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Senha atual incorreta'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Senha alterada com sucesso'
        ]);
    }

    /**
     * Desativar conta
     */
    public function deactivate(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->update(['active' => false]);

        // Revogar todos os tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Conta desativada com sucesso'
        ]);
    }

    /**
     * Estatísticas do usuário
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total_subscriptions' => $user->subscriptions()->count(),
            'active_subscriptions' => $user->subscriptions()->active()->count(),
            'total_payments' => $user->payments()->count(),
            'total_spent' => $user->payments()->completed()->sum('amount'),
            'total_reviews' => $user->reviews()->count(),
            'total_favorites' => $user->favorites()->count(),
        ];

        if ($user->isCompanion()) {
            $stats['total_earnings'] = $user->payments()->completed()->sum('amount');
            $stats['average_rating'] = $user->companionProfile?->averageRating() ?? 0;
            $stats['total_reviews_received'] = $user->companionProfile?->totalReviews() ?? 0;
        }

        return response()->json([
            'data' => $stats
        ]);
    }
}
