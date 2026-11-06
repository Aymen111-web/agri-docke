<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListFulfillmentsRequest;
use App\Http\Requests\RejectFulfillmentRequest;
use App\Http\Resources\OrderFulfillmentResource;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Services\InspectionService;
use App\Services\PayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderFulfillmentController extends Controller
{
    public function __construct(
        protected InspectionService $inspectionService = new InspectionService(),
        protected PayoutService $payoutService = new PayoutService()
    ) {}

    /**
     * List all fulfillments assigned to the authenticated farmer.
     *
     * GET /api/fulfillments?status=pending&per_page=15
     */
    public function index(ListFulfillmentsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', OrderFulfillment::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validated();

        $fulfillments = $user->orderFulfillments()
            ->with([
                'order',
                'order.buyer:id,first_name,second_name',
                'order.paymentExceptions',
                'items.listing:id,title,unit',
            ])
            ->when(isset($validated['status']), function ($query) use ($validated) {
                $query->where('status', $validated['status']);
            })
            ->orderByDesc('created_at')
            ->paginate($validated['per_page'] ?? 20);

        return OrderFulfillmentResource::collection($fulfillments)->response();
    }

    /**
     * Show a single fulfillment with full details.
     *
     * GET /api/fulfillments/{id}
     */
    public function show(int $id): JsonResponse
    {
        $fulfillment = OrderFulfillment::with([
            'order',
            'order.buyer:id,first_name,second_name',
            'order.paymentExceptions',
            'items.listing:id,title,unit,price_per_unit',
        ])->findOrFail($id);

        $this->authorize('view', $fulfillment);

        return response()->json([
            'fulfillment' => new OrderFulfillmentResource($fulfillment),
        ]);
    }

    /**
     * Complete produce inspection for a fulfillment (Buyer).
     *
     * POST /api/fulfillments/{id}/inspect
     * Body: { "inspection_status": "accepted|partially_accepted|rejected", "accepted_quantity": 85, "rejected_quantity": 15, "inspection_notes": "..." }
     */
    public function inspect(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'inspection_status' => ['required', 'string', 'in:accepted,partially_accepted,rejected'],
            'accepted_quantity' => ['nullable', 'numeric', 'min:0'],
            'rejected_quantity' => ['nullable', 'numeric', 'min:0'],
            'inspection_notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $fulfillment = OrderFulfillment::findOrFail($id);

        // Authorize buyer to inspect their order's fulfillments
        $order = $fulfillment->order;
        if (! $order || $order->buyer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to inspect this fulfillment.'], 403);
        }

        try {
            $updatedFulfillment = $this->inspectionService->completeInspection(
                $fulfillment,
                $request->input('inspection_status'),
                $request->input('accepted_quantity'),
                $request->input('rejected_quantity'),
                $request->input('inspection_notes')
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message'     => 'Produce inspection recorded successfully.',
            'fulfillment' => new OrderFulfillmentResource($updatedFulfillment->fresh(['order', 'items.listing'])),
        ]);
    }

    /**
     * Accept a pending fulfillment.
     *
     * POST /api/fulfillments/{id}/accept
     */
    public function accept(int $id): JsonResponse
    {
        $fulfillment = OrderFulfillment::findOrFail($id);

        $this->authorize('accept', $fulfillment);

        if (! in_array($fulfillment->status, ['pending', 'pending_farmer_approval'])) {
            return response()->json([
                'message' => 'Only pending fulfillments can be accepted.',
            ], 422);
        }

        $fulfillment->update([
            'status'      => 'accepted',
            'accepted_at' => now(),
        ]);

        $this->syncOrderStatus($fulfillment->order_id);

        return response()->json([
            'message'     => 'Fulfillment accepted.',
            'fulfillment' => new OrderFulfillmentResource($fulfillment->fresh([
                'order', 'items.listing',
            ])),
        ]);
    }

    /**
     * Dispatch a fulfillment (handoff to delivery driver/in transit).
     *
     * POST /api/fulfillments/{id}/dispatch
     */
    public function dispatchFulfillment(int $id): JsonResponse
    {
        $fulfillment = OrderFulfillment::findOrFail($id);

        $this->authorize('accept', $fulfillment); 

        if (! in_array($fulfillment->status, ['accepted', 'paid_in_escrow'])) {
            return response()->json([
                'message' => 'Only accepted or escrowed fulfillments can be dispatched.',
            ], 422);
        }

        $fulfillment->update([
            'status'          => 'dispatched',
            'delivery_status' => 'in_transit',
        ]);

        $this->syncOrderStatus($fulfillment->order_id);

        return response()->json([
            'message'     => 'Fulfillment dispatched successfully.',
            'fulfillment' => new OrderFulfillmentResource($fulfillment->fresh([
                'order', 'items.listing',
            ])),
        ]);
    }

    /**
     * Reject a pending fulfillment and release the reserved stock.
     *
     * POST /api/fulfillments/{id}/reject
     */
    public function reject(RejectFulfillmentRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $fulfillment = OrderFulfillment::findOrFail($id);

        $this->authorize('reject', $fulfillment);

        if (! in_array($fulfillment->status, ['pending', 'pending_farmer_approval'])) {
            return response()->json([
                'message' => 'Only pending fulfillments can be rejected.',
            ], 422);
        }

        DB::transaction(function () use ($fulfillment, $validated) {
            $items = $fulfillment->items()->get();

            foreach ($items as $item) {
                $listing = Listing::where('id', $item->listing_id)
                    ->lockForUpdate()
                    ->first();

                if ($listing) {
                    $listing->increment('quantity_available', (float) $item->quantity);
                    $listing->decrement('quantity_reserved', (float) $item->quantity);
                }
            }

            $fulfillment->update([
                'status'       => 'rejected',
                'farmer_notes' => $validated['farmer_notes'] ?? null,
                'rejected_at'  => now(),
            ]);
        });

        $this->syncOrderStatus($fulfillment->order_id);

        return response()->json([
            'message'     => 'Fulfillment rejected and reserved stock released.',
            'fulfillment' => new OrderFulfillmentResource($fulfillment->fresh([
                'order', 'items.listing',
            ])),
        ]);
    }

    /**
     * Mark an accepted fulfillment as completed (handoff done).
     *
     * POST /api/fulfillments/{id}/complete
     */
    public function complete(int $id): JsonResponse
    {
        $fulfillment = OrderFulfillment::findOrFail($id);

        $this->authorize('complete', $fulfillment);

        if (! in_array($fulfillment->status, ['accepted', 'paid_in_escrow', 'buyer_received'])) {
            return response()->json([
                'message' => 'Only accepted or escrow-paid fulfillments can be marked as completed.',
            ], 422);
        }

        DB::transaction(function () use ($fulfillment) {
            $items = $fulfillment->items()->get();

            foreach ($items as $item) {
                $listing = Listing::where('id', $item->listing_id)
                    ->lockForUpdate()
                    ->first();

                if ($listing) {
                    $listing->decrement('quantity_reserved', (float) $item->quantity);
                }
            }

            $fulfillment->update([
                'status'          => 'completed',
                'delivery_status' => 'delivered',
                'completed_at'    => now(),
            ]);
        });

        // Trigger Escrow Release (Chapa Bank Transfer)
        try {
            $payoutResult = $this->payoutService->releaseEscrow($fulfillment);
            if (!$payoutResult['success']) {
                \Illuminate\Support\Facades\Log::warning("Escrow release failed during complete()", ['reason' => $payoutResult['message']]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Escrow release crashed", ['error' => $e->getMessage()]);
        }

        $this->syncOrderStatus($fulfillment->order_id);

        return response()->json([
            'message'     => 'Fulfillment completed.',
            'fulfillment' => new OrderFulfillmentResource($fulfillment->fresh([
                'order', 'items.listing',
            ])),
        ]);
    }

    /**
     * Confirm physical receipt of produce by the buyer.
     *
     * POST /api/fulfillments/{id}/confirm-received
     *
     * The buyer confirms physical inspection & handoff.
     * Status transitions to 'buyer_received', enabling inspection release.
     */
    public function confirmReceived(int $id): JsonResponse
    {
        $fulfillment = OrderFulfillment::findOrFail($id);

        $this->authorize('confirmReceived', $fulfillment);

        if (! in_array($fulfillment->status, ['accepted', 'paid_in_escrow'])) {
            return response()->json([
                'message' => 'Produce receipt can only be confirmed for accepted or escrow-paid fulfillments.',
            ], 422);
        }

        $fulfillment->update([
            'status' => 'buyer_received',
        ]);

        $this->syncOrderStatus($fulfillment->order_id);

        return response()->json([
            'message'     => 'Produce inspection confirmed!',
            'fulfillment' => new OrderFulfillmentResource($fulfillment->fresh([
                'order', 'items.listing', 'farmer',
            ])),
        ]);
    }

    /**
     * Synchronise the parent order's aggregate status based on the current
     * state of all its fulfillments.
     */
    private function syncOrderStatus(int $orderId): void
    {
        $order = Order::with('fulfillments')->findOrFail($orderId);

        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
            return;
        }

        $fulfillments = $order->fulfillments;

        if ($fulfillments->isEmpty()) {
            return;
        }

        $statuses = $fulfillments->pluck('status');

        $allCompleted        = $statuses->every(fn ($s) => $s === 'completed');
        $allRejected         = $statuses->every(fn ($s) => $s === 'rejected');
        $hasPending          = $statuses->contains('pending') || $statuses->contains('pending_farmer_approval');
        $hasAccepted         = $statuses->contains('accepted');
        $hasPaidInEscrow     = $statuses->contains('paid_in_escrow');
        $hasDispatched       = $statuses->contains('dispatched') || $statuses->contains('in_transit');
        $hasBuyerReceived    = $statuses->contains('buyer_received');
        $hasRejected         = $statuses->contains('rejected');

        // Recalculate order total to sum non-rejected fulfillments
        if ($hasRejected) {
            $activeTotal = $fulfillments->where('status', '!=', 'rejected')->sum('subtotal_amount');
            $order->total_amount = $activeTotal;
        }

        if ($allCompleted) {
            $order->status = Order::STATUS_COMPLETED;
        } elseif ($allRejected) {
            $order->status = Order::STATUS_CANCELLED;
        } elseif ($hasBuyerReceived) {
            $order->status = Order::STATUS_PROCESSING;
        } elseif ($hasDispatched) {
            $order->status = 'dispatched';
        } elseif ($hasPaidInEscrow) {
            $order->status = Order::STATUS_PAID_IN_ESCROW;
        } elseif ($hasAccepted) {
            $order->status = Order::STATUS_AWAITING_BUYER_PAYMENT;
        } elseif ($hasPending) {
            $order->status = Order::STATUS_PENDING_FARMER_APPROVAL;
        } else {
            $hasCompleted = $statuses->contains('completed');

            if ($hasCompleted && $hasRejected) {
                $order->status = Order::STATUS_PARTIALLY_FULFILLED;
            } else {
                $order->status = Order::STATUS_PROCESSING;
            }
        }

        $order->save();
    }
}


