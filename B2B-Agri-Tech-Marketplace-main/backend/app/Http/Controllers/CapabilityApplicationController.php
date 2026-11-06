<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectCapabilityApplicationRequest;
use App\Http\Requests\StoreCapabilityApplicationRequest;
use App\Http\Resources\CapabilityApplicationResource;
use App\Models\CapabilityApplication;
use App\Models\UserCapability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CapabilityApplicationController extends Controller
{
    /**
     * Submit a capability application (farmer or buyer).
     *
     * POST /api/capability-applications
     * Body: { "capability_type": "farmer", "supporting_documents": [...] }
     */
    public function store(StoreCapabilityApplicationRequest $request): JsonResponse
    {
        $this->authorize('create', CapabilityApplication::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if the user already has an active capability of this type.
        $existingCapability = $user->capabilities()
            ->where('capability_type', $request->input('capability_type'))
            ->where('status', 'active')
            ->exists();

        if ($existingCapability) {
            return response()->json([
                'message' => 'You already have an active ' . $request->input('capability_type') . ' capability.',
            ], 409);
        }

        // Check if the user already has a pending application for this type.
        $pendingApplication = $user->capabilityApplications()
            ->where('capability_type', $request->input('capability_type'))
            ->where('status', 'pending')
            ->exists();

        if ($pendingApplication) {
            return response()->json([
                'message' => 'You already have a pending application for ' . $request->input('capability_type') . ' capability.',
            ], 409);
        }

        $rawDocs = $request->input('supporting_documents');
        $appData = $request->input('application_data');

        $filesList = [];
        if (is_array($rawDocs)) {
            foreach ($rawDocs as $k => $v) {
                if (is_string($v) && !empty($v)) {
                    $filesList[] = $v;
                } elseif (is_array($v) && (!empty($v['url']) || !empty($v['name']))) {
                    $filesList[] = $v;
                }
            }
        } elseif (is_string($rawDocs) && !empty($rawDocs)) {
            $filesList[] = $rawDocs;
        }

        $mergedPayload = [
            'files' => $filesList,
        ];

        if (is_array($appData)) {
            $mergedPayload['application_data'] = $appData;
            foreach ($appData as $k => $v) {
                $mergedPayload[$k] = $v;
            }
        }

        $application = CapabilityApplication::create([
            'user_id'              => $user->id,
            'capability_type'      => $request->validated('capability_type'),
            'supporting_documents' => $mergedPayload,
        ]);

        return response()->json([
            'message'     => 'Capability application submitted successfully.',
            'application' => new CapabilityApplicationResource($application->load('user')),
        ], 201);
    }

    /**
     * List the authenticated user's own capability applications.
     *
     * GET /api/capability-applications/my
     */
    public function my(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $applications = $user->capabilityApplications()
            ->with('reviewer:id,first_name,second_name')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'applications' => CapabilityApplicationResource::collection($applications),
        ]);
    }

    /**
     * Show a single capability application (own or admin).
     *
     * GET /api/capability-applications/{id}
     */
    public function show(int $id): JsonResponse
    {
        $application = CapabilityApplication::with(['user:id,first_name,second_name,phone', 'reviewer:id,first_name,second_name'])
            ->findOrFail($id);

        $this->authorize('view', $application);

        return response()->json([
            'application' => new CapabilityApplicationResource($application),
        ]);
    }

    /**
     * List all pending capability applications (admin only).
     *
     * GET /api/admin/capability-applications?status=pending&capability_type=farmer
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CapabilityApplication::class);

        $query = CapabilityApplication::with('user:id,first_name,second_name,phone');

        if ($request->has('status')) {
            $request->validate(['status' => ['in:pending,approved,rejected']]);
            $query->where('status', $request->input('status'));
        }

        if ($request->has('capability_type')) {
            $request->validate(['capability_type' => ['in:farmer,buyer']]);
            $query->where('capability_type', $request->input('capability_type'));
        }

        $applications = $query->orderByDesc('created_at')->paginate(20);

        return response()->json(CapabilityApplicationResource::collection($applications)->response()->getData(true));
    }

    /**
     * Approve a pending capability application (admin only).
     *
     * POST /api/admin/capability-applications/{id}/approve
     */
    public function approve(int $id): JsonResponse
    {
        $application = CapabilityApplication::findOrFail($id);

        $this->authorize('approve', $application);

        /** @var \App\Models\User $admin */
        $admin = Auth::user();

        if ($application->status !== 'pending') {
            return response()->json([
                'message' => 'This application has already been ' . $application->status . '.',
            ], 422);
        }

        DB::transaction(function () use ($application, $admin) {
            $application->update([
                'status'      => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            // Grant the capability (upsert: reactivate if previously revoked).
            UserCapability::updateOrCreate(
                [
                    'user_id'         => $application->user_id,
                    'capability_type' => $application->capability_type,
                ],
                [
                    'capability_application_id' => $application->id,
                    'status'                    => 'active',
                    'granted_by'                => $admin->id,
                    'granted_at'                => now(),
                    'revoked_at'                => null,
                ],
            );
        });

        return response()->json([
            'message'     => 'Application approved. ' . ucfirst($application->capability_type) . ' capability granted.',
            'application' => new CapabilityApplicationResource($application->fresh()->load(['user', 'capabilityGrant'])),
        ]);
    }

    /**
     * Reject a pending capability application (admin only).
     *
     * POST /api/admin/capability-applications/{id}/reject
     * Body: { "rejection_reason": "..." }
     */
    public function reject(RejectCapabilityApplicationRequest $request, int $id): JsonResponse
    {
        $application = CapabilityApplication::findOrFail($id);

        $this->authorize('reject', $application);

        /** @var \App\Models\User $admin */
        $admin = Auth::user();

        if ($application->status !== 'pending') {
            return response()->json([
                'message' => 'This application has already been ' . $application->status . '.',
            ], 422);
        }

        $application->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->validated('rejection_reason'),
            'reviewed_by'      => $admin->id,
            'reviewed_at'      => now(),
        ]);

        return response()->json([
            'message'     => 'Application rejected.',
            'application' => new CapabilityApplicationResource($application->fresh()->load('user:id,first_name,second_name,phone')),
        ]);
    }
}
