<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar novo cliente
     *
     * Cria uma nova conta de cliente na plataforma. Clientes podem buscar
     * e contratar acompanhantes, além de gerenciar favoritos e avaliações.
     *
     * @group Autenticação
     * @bodyParam name string required Nome completo do cliente. Example: João Silva
     * @bodyParam email string required Email único do cliente. Example: joao@exemplo.com
     * @bodyParam password string required Senha (mínimo 8 caracteres). Example: minhasenha123
     * @bodyParam password_confirmation string required Confirmação da senha. Example: minhasenha123
     * @bodyParam phone string Telefone do cliente (opcional). Example: (11) 99999-9999
     * @response 201 {
     *   "message": "Cliente registrado com sucesso",
     *   "user": {
     *     "id": 1,
     *     "name": "João Silva",
     *     "email": "joao@exemplo.com",
     *     "user_type": "client",
     *     "phone": "(11) 99999-9999",
     *     "active": true
     *   },
     *   "token": "1|abc123def456..."
     * }
     * @response 422 {
     *   "message": "Dados inválidos",
     *   "errors": {
     *     "email": ["Este email já está em uso."]
     *   }
     * }
     */
    public function registerClient(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
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
            'password' => Hash::make($request->password),
            'user_type' => 'client',
            'phone' => $request->phone,
            'active' => true,
        ]);

        $token = $user->createToken('auth-token', ['client'])->plainTextToken;

        return response()->json([
            'message' => 'Cliente registrado com sucesso',
            'user' => $user->makeHidden(['password']),
            'token' => $token
        ], 201);
    }

    /**
     * Registrar nova acompanhante
     *
     * Cria uma nova conta de acompanhante na plataforma. Acompanhantes podem
     * criar perfis detalhados, gerenciar serviços e preços, e interagir com clientes.
     *
     * @group Autenticação
     * @bodyParam name string required Nome completo da acompanhante. Example: Maria Silva
     * @bodyParam email string required Email único da acompanhante. Example: maria@exemplo.com
     * @bodyParam password string required Senha (mínimo 8 caracteres). Example: minhasenha123
     * @bodyParam password_confirmation string required Confirmação da senha. Example: minhasenha123
     * @bodyParam phone string Telefone da acompanhante (opcional). Example: (11) 99999-9999
     * @response 201 {
     *   "message": "Acompanhante registrada com sucesso",
     *   "user": {
     *     "id": 2,
     *     "name": "Maria Silva",
     *     "email": "maria@exemplo.com",
     *     "user_type": "companion",
     *     "phone": "(11) 99999-9999",
     *     "active": true
     *   },
     *   "token": "2|xyz789def456..."
     * }
     * @response 422 {
     *   "message": "Dados inválidos",
     *   "errors": {
     *     "email": ["Este email já está em uso."]
     *   }
     * }
     */
    public function registerCompanion(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
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
            'password' => Hash::make($request->password),
            'user_type' => 'companion',
            'phone' => $request->phone,
            'active' => true,
        ]);

        $token = $user->createToken('auth-token', ['companion'])->plainTextToken;

        return response()->json([
            'message' => 'Acompanhante registrada com sucesso',
            'user' => $user->makeHidden(['password']),
            'token' => $token
        ], 201);
    }

    /**
     * Fazer login
     *
     * Autentica um usuário (cliente, acompanhante ou administrador) na plataforma
     * e retorna um token de acesso para uso nas requisições autenticadas.
     *
     * @group Autenticação
     * @bodyParam email string required Email do usuário. Example: usuario@exemplo.com
     * @bodyParam password string required Senha do usuário. Example: minhasenha123
     * @response 200 {
     *   "message": "Login realizado com sucesso",
     *   "user": {
     *     "id": 1,
     *     "name": "João Silva",
     *     "email": "joao@exemplo.com",
     *     "user_type": "client",
     *     "active": true
     *   },
     *   "token": "1|abc123def456..."
     * }
     * @response 401 {
     *   "message": "Credenciais inválidas"
     * }
     * @response 403 {
     *   "message": "Conta desativada. Entre em contato com o suporte."
     * }
     * @response 422 {
     *   "message": "Dados inválidos",
     *   "errors": {
     *     "email": ["O campo email é obrigatório."]
     *   }
     * }
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        if (!$user->active) {
            return response()->json([
                'message' => 'Conta desativada. Entre em contato com o suporte.'
            ], 403);
        }

        // Create token with appropriate abilities based on user type
        $abilities = [$user->user_type];
        $token = $user->createToken('auth-token', $abilities)->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'user' => $user->makeHidden(['password']),
            'token' => $token
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Load companion profile if user is a companion
        if ($user->user_type === 'companion') {
            $user->load('companionProfile.city.state');
        }

        return response()->json([
            'user' => $user->makeHidden(['password'])
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'phone']);

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()->makeHidden(['password'])
        ]);
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // TODO: Implement password reset logic

        return response()->json([
            'message' => 'Password reset instructions sent to your email'
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // TODO: Implement password reset logic

        return response()->json([
            'message' => 'Password has been reset successfully'
        ]);
    }

    /**
     * List users (Admin only)
     */
    public function listUsers(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->user_type, function ($query, $type) {
                return $query->where('user_type', $type);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(15);

        return response()->json($users);
    }

    /**
     * Toggle user status (Admin only)
     */
    public function toggleUserStatus(Request $request, User $user): JsonResponse
    {
        $user->update(['active' => !$user->active]);

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Admin dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        return response()->json([
            'total_users' => User::count(),
            'total_clients' => User::where('user_type', 'client')->count(),
            'total_companions' => User::where('user_type', 'companion')->count(),
            'active_users' => User::where('active', true)->count(),
            // TODO: Add more dashboard statistics
        ]);
    }
}
