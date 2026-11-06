<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuditLogForResourceRequest;
use App\Http\Requests\ListAuditLogsRequest;
use App\Http\Requests\MyActivityRequest;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * List audit logs — admin only, with optional filters.
     *
     * GET /api/admin/audit-logs?action=listing.created&user_id=1&auditable_type=App\Models\Listing&per_page=20
     */
    public function index(ListAuditLogsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        $validated = $request->validated();

        $logs = AuditLog::with('user')
            ->when(isset($validated['action']),         fn ($q) => $q->where('action', $validated['action']))
            ->when(isset($validated['user_id']),         fn ($q) => $q->where('user_id', $validated['user_id']))
            ->when(isset($validated['auditable_type']),  fn ($q) => $q->where('auditable_type', $validated['auditable_type']))
            ->when(isset($validated['auditable_id']),    fn ($q) => $q->where('auditable_id', $validated['auditable_id']))
            ->orderByDesc('created_at')
            ->paginate($validated['per_page'] ?? 20);

        return AuditLogResource::collection($logs)->response();
    }

    /**
     * Show a single audit log entry — admin only.
     *
     * GET /api/admin/audit-logs/{auditLog}
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $this->authorize('view', $auditLog);

        $auditLog->load('user');

        return response()->json(new AuditLogResource($auditLog));
    }

    /**
     * List audit logs for a specific auditable resource — admin only.
     *
     * GET /api/admin/audit-logs/resource?type=App\Models\Order&id=5
     */
    public function forResource(AuditLogForResourceRequest $request): JsonResponse
    {
        $this->authorize('viewForResource', AuditLog::class);

        $validated = $request->validated();

        $logs = AuditLog::with('user')
            ->where('auditable_type', $validated['type'])
            ->where('auditable_id',   $validated['id'])
            ->orderByDesc('created_at')
            ->paginate($validated['per_page'] ?? 20);

        return AuditLogResource::collection($logs)->response();
    }

    /**
     * List audit logs created by a specific user — admin only.
     *
     * GET /api/admin/audit-logs/user/{userId}
     */
    public function forUser(int $userId): JsonResponse
    {
        $this->authorize('viewForUser', AuditLog::class);

        $logs = AuditLog::where('user_id', $userId)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return AuditLogResource::collection($logs)->response();
    }

    /**
     * Get the authenticated user's own activity log.
     *
     * GET /api/my-activity
     *
     * Users can only see their own logs — no admin check required.
     */
    public function myActivity(MyActivityRequest $request): JsonResponse
    {
        $this->authorize('viewOwnActivity', AuditLog::class);

        $user = Auth::user();

        $validated = $request->validated();

        $logs = AuditLog::where('user_id', $user->id)
            ->when(isset($validated['action']), fn ($q) => $q->where('action', $validated['action']))
            ->orderByDesc('created_at')
            ->paginate($validated['per_page'] ?? 20);

        return AuditLogResource::collection($logs)->response(); /////wiring resources here
    }
}
