<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacialVerification;
use App\Models\User;
use App\Services\FacialRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FacialVerificationController extends Controller
{
    private FacialRecognitionService $facialService;

    public function __construct(FacialRecognitionService $facialService)
    {
        $this->facialService = $facialService;
    }

    /**
     * Upload de documentos e fotos para verificação facial
     *
     * @group Verificação Facial
     * @authenticated
     * @bodyParam document_front file required Frente do documento (RG/CNH). Example: document.jpg
     * @bodyParam document_back file required Verso do documento (RG/CNH). Example: document_back.jpg
     * @bodyParam face_photo file required Foto do rosto da pessoa. Example: face.jpg
     * @bodyParam document_with_face file required Foto da pessoa segurando o documento. Example: document_with_face.jpg
     * @response 200 {
     *   "message": "Verificação facial enviada com sucesso",
     *   "verification": {
     *     "id": 1,
     *     "user_id": 1,
     *     "status": "pending",
     *     "created_at": "2024-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response 422 {
     *   "message": "Dados inválidos",
     *   "errors": {
     *     "document_front": ["O arquivo é obrigatório."]
     *   }
     * }
     */
    public function uploadVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'document_front' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240', // 10MB
            'document_back' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'face_photo' => 'required|file|image|max:10240',
            'document_with_face' => 'required|file|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            // Verificar se já existe uma verificação aprovada
            $existingVerification = $user->facialVerification;
            if ($existingVerification && $existingVerification->isApproved()) {
                return response()->json([
                    'message' => 'Verificação facial já foi aprovada'
                ], 400);
            }

            // Processar verificação
            $verification = $this->facialService->processVerification(
                $user,
                $request->file('document_front'),
                $request->file('document_back'),
                $request->file('face_photo'),
                $request->file('document_with_face')
            );

            return response()->json([
                'message' => 'Verificação facial enviada com sucesso',
                'verification' => $verification->only(['id', 'user_id', 'status', 'created_at'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao processar verificação facial',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login usando reconhecimento facial
     *
     * @group Verificação Facial
     * @bodyParam email string required Email do usuário. Example: user@example.com
     * @bodyParam face_image file required Foto do rosto para verificação. Example: face.jpg
     * @response 200 {
     *   "message": "Login facial realizado com sucesso",
     *   "user": {
     *     "id": 1,
     *     "name": "João Silva",
     *     "email": "user@example.com",
     *     "user_type": "client"
     *   },
     *   "token": "1|abc123def456..."
     * }
     * @response 401 {
     *   "message": "Verificação facial falhou"
     * }
     */
    public function faceLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'face_image' => 'required|file|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            if (!$user->active) {
                return response()->json([
                    'message' => 'Conta desativada'
                ], 403);
            }

            // Verificar se usuário tem verificação facial aprovada
            $verification = $user->facialVerification;
            if (!$verification || !$verification->isApproved()) {
                return response()->json([
                    'message' => 'Verificação facial não aprovada'
                ], 401);
            }

            // Verificar face
            $isValid = $this->facialService->verifyFace($user, $request->file('face_image'));

            if (!$isValid) {
                return response()->json([
                    'message' => 'Verificação facial falhou'
                ], 401);
            }

            // Criar token de autenticação
            $abilities = [$user->user_type];
            $token = $user->createToken('face-auth-token', $abilities)->plainTextToken;

            return response()->json([
                'message' => 'Login facial realizado com sucesso',
                'user' => $user->makeHidden(['password']),
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro no login facial',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter status da verificação facial do usuário
     *
     * @group Verificação Facial
     * @authenticated
     * @response 200 {
     *   "verification": {
     *     "id": 1,
     *     "status": "pending",
     *     "created_at": "2024-01-15T10:00:00.000000Z",
     *     "verified_at": null,
     *     "rejection_reason": null
     *   }
     * }
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $verification = $user->facialVerification;

        if (!$verification) {
            return response()->json([
                'verification' => null
            ]);
        }

        return response()->json([
            'verification' => $verification->only([
                'id', 'status', 'created_at', 'verified_at', 'rejection_reason'
            ])
        ]);
    }

    /**
     * Validar se uma imagem contém um rosto
     *
     * @group Verificação Facial
     * @bodyParam face_image file required Imagem para validação. Example: face.jpg
     * @response 200 {
     *   "valid": true,
     *   "message": "Imagem contém um rosto válido"
     * }
     * @response 422 {
     *   "valid": false,
     *   "message": "Nenhum rosto detectado na imagem"
     * }
     */
    public function validateFaceImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'face_image' => 'required|file|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => 'Arquivo inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $isValid = $this->facialService->validateFaceImage($request->file('face_image'));

            return response()->json([
                'valid' => $isValid,
                'message' => $isValid
                    ? 'Imagem contém um rosto válido'
                    : 'Nenhum rosto detectado na imagem'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Erro ao validar imagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprovar verificação facial (apenas admin)
     *
     * @group Verificação Facial
     * @authenticated
     * @bodyParam verification_id integer required ID da verificação. Example: 1
     * @response 200 {
     *   "message": "Verificação aprovada com sucesso"
     * }
     */
    public function approveVerification(Request $request): JsonResponse
    {
        // Verificar se usuário é admin
        if ($request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Acesso negado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|integer|exists:facial_verifications,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $verification = FacialVerification::findOrFail($request->verification_id);
            $this->facialService->approveVerification($verification);

            return response()->json([
                'message' => 'Verificação aprovada com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao aprovar verificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rejeitar verificação facial (apenas admin)
     *
     * @group Verificação Facial
     * @authenticated
     * @bodyParam verification_id integer required ID da verificação. Example: 1
     * @bodyParam reason string required Motivo da rejeição. Example: Documento ilegível
     * @response 200 {
     *   "message": "Verificação rejeitada com sucesso"
     * }
     */
    public function rejectVerification(Request $request): JsonResponse
    {
        // Verificar se usuário é admin
        if ($request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Acesso negado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|integer|exists:facial_verifications,id',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $verification = FacialVerification::findOrFail($request->verification_id);
            $this->facialService->rejectVerification($verification, $request->reason);

            return response()->json([
                'message' => 'Verificação rejeitada com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao rejeitar verificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar verificações pendentes (apenas admin)
     *
     * @group Verificação Facial
     * @authenticated
     * @response 200 {
     *   "verifications": [
     *     {
     *       "id": 1,
     *       "user": {
     *         "id": 1,
     *         "name": "João Silva",
     *         "email": "joao@example.com"
     *       },
     *       "status": "pending",
     *       "created_at": "2024-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function listPendingVerifications(Request $request): JsonResponse
    {
        // Verificar se usuário é admin
        if ($request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Acesso negado'
            ], 403);
        }

        try {
            // Buscar todas as verificações, não apenas pendentes
            $verifications = FacialVerification::with('user:id,name,email')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'verifications' => $verifications->map(function ($verification) {
                    return [
                        'id' => $verification->id,
                        'user' => $verification->user->only(['id', 'name', 'email']),
                        'status' => $verification->status,
                        'created_at' => $verification->created_at,
                        'document_front_path' => $verification->document_front_path,
                        'document_back_path' => $verification->document_back_path,
                        'face_photo_path' => $verification->face_photo_path,
                        'document_with_face_path' => $verification->document_with_face_path,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar verificações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Servir imagem de verificação facial (apenas para admins)
     */
    public function serveVerificationImage(Request $request, int $verificationId, string $imageType): mixed
    {
        // Verificar se usuário é admin
        if ($request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Acesso negado'
            ], 403);
        }

        try {
            $verification = FacialVerification::findOrFail($verificationId);

            $imagePath = match ($imageType) {
                'document_front' => $verification->document_front_path,
                'document_back' => $verification->document_back_path,
                'face_photo' => $verification->face_photo_path,
                'document_with_face' => $verification->document_with_face_path,
                default => null,
            };

            if (!$imagePath || !Storage::disk('private')->exists($imagePath)) {
                return response()->json([
                    'message' => 'Imagem não encontrada'
                ], 404);
            }

            $file = Storage::disk('private')->get($imagePath);
            $mimeType = Storage::disk('private')->mimeType($imagePath);

            return response($file)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar imagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
