<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdminTicketController extends Controller
{
    /**
     * Get all tickets with pagination and filters (admin only).
     */
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::with(['user', 'booking']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Get paginated results
        $perPage = $request->get('per_page', 15);
        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $tickets->items(),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'last_page' => $tickets->lastPage(),
            ],
        ]);
    }

    /**
     * Show a specific ticket (admin only).
     */
    public function show(SupportTicket $ticket): JsonResponse
    {
        return response()->json([
            'data' => $ticket->load(['user', 'booking', 'messages']),
        ]);
    }

    /**
     * Update ticket status (admin only).
     */
    public function updateStatus(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:open,answered,resolved,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $ticket->update([
            'status' => $request->status,
            'last_reply_at' => now(),
        ]);

        return response()->json([
            'message' => 'Status do ticket atualizado com sucesso',
            'data' => $ticket->load(['user', 'booking']),
        ]);
    }

    /**
     * Respond to a ticket (admin only).
     */
    public function respond(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'response' => 'required|string|max:5000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,webp,gif|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('admin-ticket-attachments', 'public');
                $attachments[] = Storage::disk('public')->url($path);
            }
        }

        // Create a message for the ticket
        $ticket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->response,
            'is_admin' => true,
            'attachments' => $attachments,
        ]);

        // Update ticket status and last reply time
        $ticket->update([
            'status' => 'answered',
            'last_reply_at' => now(),
        ]);

        return response()->json([
            'message' => 'Resposta enviada com sucesso',
            'data' => $ticket->load(['user', 'booking', 'messages']),
        ]);
    }

    /**
     * Get ticket statistics (admin only).
     */
    public function stats(): JsonResponse
    {
        $totalTickets = SupportTicket::count();
        $openTickets = SupportTicket::where('status', 'open')->count();
        $inProgressTickets = SupportTicket::where('status', 'in_progress')->count();
        $resolvedTickets = SupportTicket::where('status', 'resolved')->count();
        $closedTickets = SupportTicket::where('status', 'closed')->count();

        return response()->json([
            'data' => [
                'total' => $totalTickets,
                'open' => $openTickets,
                'in_progress' => $inProgressTickets,
                'resolved' => $resolvedTickets,
                'closed' => $closedTickets,
            ],
        ]);
    }

    /**
     * Upload image for ticket (admin only).
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|mimes:jpg,jpeg,png,webp,gif|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('image');
            $path = $file->store('admin-ticket-images', 'public');
            $url = Storage::disk('public')->url($path);

            return response()->json([
                'message' => 'Imagem enviada com sucesso',
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao fazer upload da imagem',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
