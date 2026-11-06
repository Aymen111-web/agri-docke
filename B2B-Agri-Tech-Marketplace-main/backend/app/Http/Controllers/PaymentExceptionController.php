<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListPaymentExceptionsRequest;
use App\Http\Requests\ResolvePaymentExceptionRequest;
use App\Http\Requests\StorePaymentExceptionRequest;
use App\Http\Resources\PaymentExceptionResource;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentException;
use App\Models\Payout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentExceptionController extends Controller
{
    /**
     * Raise a payment exception (dispute, refund request, etc.).
     *
     * POST /api/payment-exceptions
     * Body: {
     *   "payment_id": 1,
     *   "type": "dispute",
     *   "description": "I was charged but the order was never fulfilled."
     * }
     *
     * Any authenticated user who is part of the order (buyer or farmer)
     * can raise an exception against a payment.
     */
    public function store(StorePaymentExceptionRequest $request): JsonResponse
    {
        $this->authorize('create', PaymentException::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validated();

        $payment = null;
        $order   = null;

        if (! empty($validated['payment_id'])) {
            $payment = Payment::with('order')->find($validated['payment_id']);
            if ($payment) {
                $order = $payment->order;
            }
        }

        if (! $order && ! empty($validated['order_id'])) {
            $order = Order::with('payment', 'payments')->find($validated['order_id']);
            if ($order) {
                $payment = $order->payment ?? $order->payments()->latest()->first();
            }
        }

        // If no payment record exists for this order yet, auto-create an escrow payment record!
        if ($order && ! $payment) {
            $payment = Payment::create([
                'order_id'       => $order->id,
                'buyer_id'       => $order->buyer_id,
                'amount'         => $order->total_amount,
                'currency'       => 'ETB',
                'status'         => 'escrow_held',
                'payment_method' => 'chapa',
                'chapa_tx_ref'   => 'CHP-TX-' . $order->id . '-' . time(),
            ]);
        }

        if (! $order || ! $payment) {
            return response()->json([
                'message' => 'Unable to locate a valid order or payment for this dispute.',
            ], 422);
        }

        // Only the buyer or a farmer assigned to a fulfillment on this order may raise an exception.
        if (! $this->isOrderParticipant($user, $order)) {
            return response()->json([
                'message' => 'You are not authorized to raise an exception for this payment.',
            ], 403);
        }

        // Prevent duplicate open exceptions for the same payment.
        $existingOpen = PaymentException::where('payment_id', $payment->id)
            ->whereIn('status', ['open', 'investigating'])
            ->exists();

        if ($existingOpen) {
            return response()->json([
                'message' => 'An open dispute already exists for this order.',
            ], 409);
        }

        $exception = PaymentException::create([
            'payment_id'  => $payment->id,
            'order_id'    => $order->id,
            'raised_by'   => $user->id,
            'type'        => $validated['type'],
            'description' => $validated['description'],
            'status'      => 'open',
        ]);

        return response()->json([
            'message'           => 'Payment exception raised successfully.',
            'payment_exception' => new PaymentExceptionResource($exception->load([
                'payment', 'order', 'raisedBy', 'resolvedBy',
            ])),
        ], 201);
    }

    /**
     * List the authenticated user's own payment exceptions.
     *
     * GET /api/payment-exceptions/my?status=open&per_page=20
     */
    public function my(ListPaymentExceptionsRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validated();

        $exceptions = PaymentException::where('raised_by', $user->id)
            ->with([
                'payment:id,chapa_tx_ref,amount,currency,status',
                'order:id,order_number,status',
            ])
            ->when(isset($validated['status']), function ($query) use ($validated) {
                $query->where('status', $validated['status']);
            })
            ->orderByDesc('created_at')
            ->paginate($validated['per_page'] ?? 20);

        return PaymentExceptionResource::collection($exceptions)->response();
    }

    /**
     * Show a single payment exception (own or admin).
     *
     * GET /api/payment-exceptions/{id}
     */
    public function show(int $id): JsonResponse
    {
        $exception = PaymentException::with([
            'payment:id,chapa_tx_ref,amount,currency,status',
            'order:id,order_number,status,total_amount,currency',
            'raisedBy:id,first_name,second_name,phone',
            'resolvedBy:id,first_name,second_name',
        ])->findOrFail($id);

        $this->authorize('view', $exception);

        return response()->json([
            'payment_exception' => new PaymentExceptionResource($exception),
        ]);
    }

    /**
     * List all payment exceptions (admin only).
     *
     * GET /api/admin/payment-exceptions?status=open&type=dispute&per_page=20
     */
    public function index(ListPaymentExceptionsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', PaymentException::class);

        $validated = $request->validated();

        $query = PaymentException::with([
            'payment:id,chapa_tx_ref,amount,currency,status',
            'order:id,order_number,status',
            'raisedBy:id,first_name,second_name,phone',
        ]);

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        $exceptions = $query->orderByDesc('created_at')
            ->paginate($validated['per_page'] ?? 20);

        return PaymentExceptionResource::collection($exceptions)->response();
    }

    /**
     * Move an open exception to "investigating" status (admin only).
     *
     * POST /api/admin/payment-exceptions/{id}/investigate
     */
    public function investigate(int $id): JsonResponse
    {
        $exception = PaymentException::findOrFail($id);

        $this->authorize('investigate', $exception);

        if ($exception->status !== 'open') {
            return response()->json([
                'message' => 'Only open exceptions can be moved to investigating.',
            ], 422);
        }

        $exception->update([
            'status' => 'investigating',
        ]);

        return response()->json([
            'message'           => 'Exception is now under investigation.',
            'payment_exception' => new PaymentExceptionResource($exception->fresh()->load([
                'payment', 'order', 'raisedBy',
            ])),
        ]);
    }

    /**
     * Resolve a payment exception (admin only).
     *
     * POST /api/admin/payment-exceptions/{id}/resolve
     * Body: { "resolution_notes": "Refund processed via Chapa transfer #XYZ." }
     *
     * NOTE: Resolving an exception NEVER directly mutates payments.status.
     * Any refund/reversal must go through the Chapa API, and the resulting
     * webhook event will handle the payment status transition.
     */
    public function resolve(ResolvePaymentExceptionRequest $request, int $id): JsonResponse
    {
        $exception = PaymentException::findOrFail($id);

        $this->authorize('resolve', $exception);

        /** @var \App\Models\User $admin */
        $admin = Auth::user();

        $validated = $request->validated();

        if (! in_array($exception->status, ['open', 'investigating', 'resolved'], true)) {
            return response()->json([
                'message' => 'Only open, investigating, or resolved exceptions can be processed.',
            ], 422);
        }

        $action = $validated['action'] ?? 'refund_buyer';

        $exception->update([
            'status'           => 'resolved',
            'resolution_notes' => $validated['resolution_notes'],
            'resolved_by'      => $admin->id,
            'resolved_at'      => now(),
        ]);

        $order = $exception->order;
        if ($order) {
            if ($action === 'refund_buyer') {
                $order->update([
                    'status'         => 'cancelled',
                    'payment_status' => 'refunded',
                    'payout_status'  => 'refunded',
                ]);

                if ($order->payment) {
                    $order->payment->update(['status' => 'refunded']);
                }

                if ($order->payments) {
                    foreach ($order->payments as $p) {
                        $p->update(['status' => 'refunded']);
                    }
                }

                foreach ($order->fulfillments as $fulfillment) {
                    $fulfillment->update([
                        'payout_status' => 'refunded',
                        'status'        => 'rejected',
                    ]);
                }

                AuditLog::create([
                    'user_id'        => $admin->id,
                    'action'         => 'escrow_refunded_to_buyer',
                    'auditable_type' => Order::class,
                    'auditable_id'   => $order->id,
                    'new_values'     => [
                        'exception_id' => $exception->id,
                        'notes'        => $validated['resolution_notes'],
                        'amount'       => $order->total_amount,
                    ],
                    'ip_address'     => request()->ip(),
                ]);
            } elseif ($action === 'release_farmer') {
                $order->update([
                    'status'         => 'completed',
                    'payout_status'  => 'eligible',
                    'payment_status' => 'paid_in_escrow',
                ]);

                if ($order->payment) {
                    $order->payment->update(['status' => 'paid_in_escrow']);
                }

                // If fulfillments exist on order, update fulfillments & create Payout records
                if ($order->fulfillments && $order->fulfillments->count() > 0) {
                    foreach ($order->fulfillments as $fulfillment) {
                        $fulfillment->update([
                            'payout_status' => 'eligible',
                            'status'        => 'completed',
                            'completed_at'  => now(),
                        ]);

                        Payout::updateOrCreate(
                            ['order_fulfillment_id' => $fulfillment->id],
                            [
                                'farmer_id'    => $fulfillment->farmer_id,
                                'amount'       => $fulfillment->subtotal_amount ?: $order->total_amount,
                                'status'       => 'processed',
                                'reference'    => 'ESCROW-RELEASE-' . strtoupper(uniqid()),
                                'processed_at' => now(),
                            ]
                        );
                    }
                } else {
                    // Fallback: If no fulfillment record exists, locate farmer from order items or fallback
                    $farmerId = $order->items()->first()?->listing?->farmer_id;
                    if ($farmerId) {
                        $fulfillment = $order->fulfillments()->create([
                            'farmer_id'         => $farmerId,
                            'status'            => 'completed',
                            'delivery_status'   => 'delivered',
                            'inspection_status' => 'accepted',
                            'payout_status'     => 'eligible',
                            'subtotal_amount'   => $order->total_amount,
                            'produce_amount'    => $order->total_amount,
                            'farmer_net_payout' => $order->total_amount,
                            'completed_at'      => now(),
                        ]);

                        Payout::updateOrCreate(
                            ['order_fulfillment_id' => $fulfillment->id],
                            [
                                'farmer_id'    => $farmerId,
                                'amount'       => $order->total_amount,
                                'status'       => 'processed',
                                'reference'    => 'ESCROW-RELEASE-' . strtoupper(uniqid()),
                                'processed_at' => now(),
                            ]
                        );
                    }
                }

                AuditLog::create([
                    'user_id'        => $admin->id,
                    'action'         => 'escrow_released_to_farmer',
                    'auditable_type' => Order::class,
                    'auditable_id'   => $order->id,
                    'new_values'     => [
                        'exception_id' => $exception->id,
                        'notes'        => $validated['resolution_notes'],
                        'amount'       => $order->total_amount,
                    ],
                    'ip_address'     => request()->ip(),
                ]);
            }
        }

        return response()->json([
            'message'           => 'Exception resolved.',
            'payment_exception' => new PaymentExceptionResource($exception->fresh()->load([
                'payment', 'order', 'raisedBy', 'resolvedBy',
            ])),
        ]);
    }

    /**
     * Reject a payment exception (admin only).
     *
     * POST /api/admin/payment-exceptions/{id}/reject
     * Body: { "resolution_notes": "No evidence of mismatch found." }
     */
    public function reject(ResolvePaymentExceptionRequest $request, int $id): JsonResponse
    {
        $exception = PaymentException::findOrFail($id);

        $this->authorize('reject', $exception);

        /** @var \App\Models\User $admin */
        $admin = Auth::user();

        $validated = $request->validated();

        if (! in_array($exception->status, ['open', 'investigating', 'resolved'], true)) {
            return response()->json([
                'message' => 'Only open, investigating, or resolved exceptions can be rejected.',
            ], 422);
        }

        $exception->update([
            'status'           => 'rejected',
            'resolution_notes' => $validated['resolution_notes'],
            'resolved_by'      => $admin->id,
            'resolved_at'      => now(),
        ]);

        return response()->json([
            'message'           => 'Exception rejected.',
            'payment_exception' => new PaymentExceptionResource($exception->fresh()->load([
                'payment', 'order', 'raisedBy', 'resolvedBy',
            ])),
        ]);
    }

    /**
     * Respond to a payment exception (Farmer / Counter-Statement).
     *
     * POST /api/payment-exceptions/{id}/respond
     */
    public function respondToException(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'farmer_response' => ['required', 'string', 'max:2000'],
        ]);

        $exception = PaymentException::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->isOrderParticipant($user, $exception->order)) {
            return response()->json([
                'message' => 'Unauthorized to respond to this dispute.',
            ], 403);
        }

        $exception->update([
            'farmer_response' => $request->input('farmer_response'),
            'status'          => $exception->status === 'open' ? 'investigating' : $exception->status,
        ]);

        return response()->json([
            'message'           => 'Counter-statement submitted to Admin.',
            'payment_exception' => new PaymentExceptionResource($exception->fresh()->load([
                'payment', 'order', 'raisedBy', 'resolvedBy',
            ])),
        ]);
    }

    /**
     * Check whether the given user is a participant in the order
     * (either the buyer or a farmer assigned to a fulfillment).
     *
     * This is intentionally kept in the controller because it requires the
     * resolved Order instance — a concern too contextual for the policy.
     */
    private function isOrderParticipant(\App\Models\User $user, Order $order): bool
    {
        // Is the buyer?
        if ($order->buyer_id === $user->id) {
            return true;
        }

        // Is a farmer on one of the fulfillments?
        return $order->fulfillments()
            ->where('farmer_id', $user->id)
            ->exists();
    }
}
