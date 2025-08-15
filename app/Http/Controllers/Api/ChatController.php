<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\CompanionProfile;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SecurityDetectionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function __construct(private NotificationService $notificationService, private SecurityDetectionService $securityService)
    {
    }

    /**
     * Listar conversas do usuário
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        $conversations = ChatConversation::with([
            'client',
            'companion.companionProfile',
            'messages' => function ($query) {
                $query->latest()->limit(1);
            }
        ])
        ->forUser($user->id)
        ->active()
        ->orderBy('last_message_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $conversations->items(),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
                'last_page' => $conversations->lastPage(),
            ]
        ]);
    }

    /**
     * Iniciar ou obter conversa com acompanhante
     */
    public function startConversation(Request $request): JsonResponse
    {
        $user = $request->user();

        // Verificar se o usuário é cliente
        if ($user->user_type !== 'client') {
            return response()->json([
                'message' => 'Apenas clientes podem iniciar conversas'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'companion_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $companionId = $request->companion_id;

        // Verificar se o usuário alvo é uma acompanhante
        $companion = User::where('id', $companionId)
            ->where('user_type', 'companion')
            ->first();

        if (!$companion) {
            return response()->json([
                'message' => 'Usuário não é uma acompanhante válida'
            ], 404);
        }

        // Verificar se já existe uma conversa
        $conversation = ChatConversation::where(function ($query) use ($user, $companionId) {
            $query->where('client_id', $user->id)
                  ->where('companion_id', $companionId);
        })->first();

        if (!$conversation) {
            // Criar nova conversa
            $conversation = ChatConversation::create([
                'client_id' => $user->id,
                'companion_id' => $companionId,
                'status' => 'active',
            ]);
        }

        return response()->json([
            'data' => $conversation->load(['client', 'companion.companionProfile'])
        ]);
    }

    /**
     * Enviar mensagem
     */
    public function sendMessage(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'message_type' => 'sometimes|string|in:text,service_request'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $conversation = ChatConversation::findOrFail($conversationId);

        // Verificar se o usuário é participante da conversa
        if (!$conversation->isParticipant($user->id)) {
            return response()->json([
                'message' => 'Você não tem permissão para enviar mensagens nesta conversa'
            ], 403);
        }

        // Criar mensagem
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content' => $request->content,
            'message_type' => $request->message_type ?? 'text',
            'metadata' => $request->metadata ?? null,
        ]);

        // Notificar o outro participante em tempo real
        $recipientId = $conversation->client_id === $user->id ? $conversation->companion_id : $conversation->client_id;
        $this->notificationService->notify(
            $recipientId,
            'Nova mensagem no chat',
            mb_strimwidth($message->content, 0, 80, '...'),
            'chat.message',
            ['conversation_id' => $conversation->id, 'message_id' => $message->id]
        );

        // Atualizar última mensagem da conversa
        $conversation->updateLastMessage();

        // Analisar segurança da mensagem
        $securityAlerts = $this->securityService->analyzeMessage(
            $request->content,
            $conversation->id,
            $user->id
        );

        // Se houver alertas de segurança, criar mensagens de alerta
        foreach ($securityAlerts as $alert) {
            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => 1, // Sistema
                'content' => $alert->metadata['warning_message'],
                'message_type' => 'security_alert',
                'metadata' => [
                    'alert_id' => $alert->id,
                    'alert_type' => $alert->alert_type,
                    'severity' => $alert->severity,
                    'recommendation' => $alert->metadata['recommendation'] ?? null,
                ],
            ]);
        }

        return response()->json([
            'data' => $message->load('sender'),
            'security_alerts' => count($securityAlerts)
        ]);
    }

    /**
     * Obter mensagens de uma conversa
     */
    public function messages(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 50);

        $conversation = ChatConversation::findOrFail($conversationId);

        // Verificar se o usuário é participante da conversa
        if (!$conversation->isParticipant($user->id)) {
            return response()->json([
                'message' => 'Você não tem permissão para acessar esta conversa'
            ], 403);
        }

        $messages = ChatMessage::with('sender')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Marcar mensagens como lidas
        $messages->getCollection()->each(function ($message) use ($user) {
            if (!$message->isFromUser($user->id) && !$message->is_read) {
                $message->markAsRead();
            }
        });

        return response()->json([
            'data' => $messages->items(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'last_page' => $messages->lastPage(),
            ]
        ]);
    }

    /**
     * Marcar mensagens como lidas
     */
    public function markAsRead(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();

        $conversation = ChatConversation::findOrFail($conversationId);

        // Verificar se o usuário é participante da conversa
        if (!$conversation->isParticipant($user->id)) {
            return response()->json([
                'message' => 'Você não tem permissão para acessar esta conversa'
            ], 403);
        }

        // Marcar todas as mensagens não lidas como lidas
        ChatMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'message' => 'Mensagens marcadas como lidas'
        ]);
    }

    /**
     * Solicitar serviço via chat
     */
    public function requestService(Request $request): JsonResponse
    {
        $user = $request->user();

        // Verificar se o usuário é cliente
        if ($user->user_type !== 'client') {
            return response()->json([
                'message' => 'Apenas clientes podem solicitar serviços'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:chat_conversations,id',
            'service_id' => 'required|exists:companion_services,id',
            'message' => 'sometimes|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $conversation = ChatConversation::with('companion.companionProfile.services')
            ->findOrFail($request->conversation_id);

        // Verificar se o usuário é participante da conversa
        if (!$conversation->isParticipant($user->id)) {
            return response()->json([
                'message' => 'Você não tem permissão para acessar esta conversa'
            ], 403);
        }

        // Verificar se o serviço pertence à acompanhante
        $service = $conversation->companion->companionProfile->services
            ->where('id', $request->service_id)
            ->first();

        if (!$service) {
            return response()->json([
                'message' => 'Serviço não encontrado'
            ], 404);
        }

        // Criar mensagem de solicitação de serviço
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content' => $request->message ?? "Gostaria de contratar o serviço: {$service->service_type->name}",
            'message_type' => 'service_request',
            'metadata' => [
                'service' => [
                    'id' => $service->id,
                    'name' => $service->service_type->name,
                    'price' => $service->price,
                    'description' => $service->description,
                ]
            ],
        ]);

        // Atualizar última mensagem da conversa
        $conversation->updateLastMessage();

        // Notificar acompanhante sobre solicitação de serviço
        $this->notificationService->notify(
            $conversation->companion_id,
            'Solicitação de serviço',
            $message->content,
            'service.request',
            ['conversation_id' => $conversation->id, 'service_id' => $service->id]
        );

        return response()->json([
            'data' => $message->load('sender'),
            'service' => $service
        ]);
    }

    /**
     * Obter estatísticas do chat
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = ChatConversation::forUser($user->id)->active();
        $totalConversations = $conversations->count();

        $unreadMessages = ChatMessage::whereHas('conversation', function ($query) use ($user) {
            $query->forUser($user->id);
        })
        ->where('sender_id', '!=', $user->id)
        ->where('is_read', false)
        ->count();

        $securityAlerts = \App\Models\SecurityAlert::whereHas('conversation', function ($query) use ($user) {
            $query->forUser($user->id);
        })
        ->unresolved()
        ->count();

        return response()->json([
            'data' => [
                'total_conversations' => $totalConversations,
                'unread_messages' => $unreadMessages,
                'security_alerts' => $securityAlerts,
            ]
        ]);
    }

    /**
     * Obter status online dos participantes das conversas
     */
    public function onlineStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = ChatConversation::with(['client', 'companion'])
            ->forUser($user->id)
            ->active()
            ->get();

        $onlineStatus = [];

        foreach ($conversations as $conversation) {
            $otherParticipant = $user->user_type === 'client'
                ? $conversation->companion
                : $conversation->client;

            if ($otherParticipant) {
                $onlineStatus[$conversation->id] = [
                    'user_id' => $otherParticipant->id,
                    'is_online' => $otherParticipant->isOnline(),
                    'last_active' => $otherParticipant->last_active_at,
                    'name' => $otherParticipant->name
                ];
            }
        }

        return response()->json([
            'data' => $onlineStatus
        ]);
    }
}
