<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($request->get('limit', 20))
            ->get();

        $unreadCount = Notification::where('user_id', $user->id)->whereNull('read_at')->count();

        return response()->json([
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        Notification::where('user_id', $user->id)->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not allowed'], 403);
        }
        $notification->update(['read_at' => now()]);
        return response()->json(['message' => 'Notification marked as read']);
    }

    // Helper endpoint (admin) to create notification
    public function sendToUser(Request $request, User $user): JsonResponse
    {
        $this->authorize('admin');
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $request->get('type'),
            'title' => $request->get('title', 'Notificação'),
            'body' => $request->get('body'),
            'data' => $request->get('data', []),
        ]);
        return response()->json(['message' => 'Notification created', 'data' => $notification]);
    }
}
