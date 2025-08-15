<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SupportTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::with('booking')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($tickets);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
            'priority' => 'nullable|in:low,normal,high',
            'booking_id' => 'nullable|exists:bookings,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,webp,gif,pdf|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'booking_id' => $request->booking_id,
            'subject' => $request->subject,
            'message' => $request->message,
            'priority' => $request->priority ?? 'normal',
            'status' => 'open',
            'last_reply_at' => now(),
        ]);

        // Upload attachments if present
        $uploaded = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('support-attachments', 'public');
                $uploaded[] = $path;
            }
            // store paths in a json column if exists or meta file; as quick compat, append to message
            if (!empty($uploaded)) {
                $ticket->message = $ticket->message . "\n\nArquivos: " . implode(', ', array_map(fn($p) => Storage::disk('public')->url($p), $uploaded));
                $ticket->save();
            }
        }

        return response()->json([
            'message' => 'Ticket criado com sucesso',
            'data' => $ticket,
        ], 201);
    }

    public function close(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not allowed'], 403);
        }

        $ticket->update(['status' => 'closed']);

        return response()->json(['message' => 'Ticket fechado com sucesso']);
    }
}
