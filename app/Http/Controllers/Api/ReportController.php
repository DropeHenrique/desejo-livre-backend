<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Get all reports with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Report::with(['reporter', 'reportedUser']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        // Get paginated results
        $perPage = $request->get('per_page', 15);
        $reports = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'last_page' => $reports->lastPage(),
            ],
        ]);
    }

    /**
     * Store a new report.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reported_user_id' => 'nullable|exists:users,id',
            'reported_content_type' => 'nullable|string|in:profile,photo,video,message,review,comment',
            'reported_content_id' => 'nullable|integer',
            'reported_content_description' => 'nullable|string|max:500',
            'reason' => 'required|string|in:inappropriate_content,spam,harassment,fake_profile,illegal_activity,copyright,other',
            'description' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $report = Report::create([
            'reporter_id' => auth()->id(),
            'reported_user_id' => $request->reported_user_id,
            'reported_content_type' => $request->reported_content_type,
            'reported_content_id' => $request->reported_content_id,
            'reported_content_description' => $request->reported_content_description,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Denúncia enviada com sucesso',
            'data' => $report->load(['reporter', 'reportedUser']),
        ], 201);
    }

    /**
     * Show a specific report.
     */
    public function show(Report $report): JsonResponse
    {
        return response()->json([
            'data' => $report->load(['reporter', 'reportedUser']),
        ]);
    }

    /**
     * Update report status (admin only).
     */
    public function updateStatus(Request $request, Report $report): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,investigating,resolved,dismissed',
            'action' => 'nullable|string|max:100',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $report->update([
            'status' => $request->status,
            'action_taken' => $request->action,
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json([
            'message' => 'Status da denúncia atualizado com sucesso',
            'data' => $report->load(['reporter', 'reportedUser']),
        ]);
    }

    /**
     * Get report statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Report::count(),
            'pending' => Report::pending()->count(),
            'investigating' => Report::investigating()->count(),
            'resolved' => Report::resolved()->count(),
            'dismissed' => Report::dismissed()->count(),
            'by_reason' => Report::selectRaw('reason, COUNT(*) as count')
                ->groupBy('reason')
                ->get()
                ->pluck('count', 'reason')
                ->toArray(),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get reports made by the authenticated user.
     */
    public function myReports(): JsonResponse
    {
        $reports = Report::where('reporter_id', auth()->id())
            ->with(['reportedUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'last_page' => $reports->lastPage(),
            ],
        ]);
    }
}
