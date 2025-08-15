<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    /**
     * List bookings for the authenticated companion
     */
    public function companionIndex(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->companionProfile()->firstOrFail();

        $query = Booking::with(['client', 'serviceType'])
            ->where('companion_profile_id', $profile->id);

        if ($request->filled('start_date')) {
            $query->where('starts_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->where('starts_at', '<=', $request->input('end_date'));
        }

        $bookings = $query->orderBy('starts_at', 'asc')
            ->paginate($request->per_page ?? 20);

        return response()->json($bookings);
    }
}
