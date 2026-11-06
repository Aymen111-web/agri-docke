<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListOrdersRequest;
use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Models\Order;
use App\Services\DeliveryService;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService = new ReservationService(),
        protected DeliveryService $deliveryService = new DeliveryService()
    ) {}

    /**
     * List the authenticated buyer's orders.
     *
     * GET /api/orders?status=pending_payment&per_page=15
     */
    public function index(ListOrdersRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validated();

        $orders = $user->orders()
            ->with([
                'items.listing.farmer',
                'fulfillments.farmer',
                'payment',
                'buyer',
            ])
            ->when(isset($validated['status']), function ($query) use ($validated) {
                $query->where('status', $validated['status']);
            })
            ->orderByDesc('placed_at')
            ->paginate($validated['per_page'] ?? 20);

        return OrderResource::collection($orders)->response();
    }

    /**
     * Show a single order with full details.
     *
     * GET /api/orders/{id}
     */
    public function show(int $id): JsonResponse
    {
        $order = Order::with([
            'items.listing:id,farmer_id,title,unit,harvest_date,quality_grade,minimum_order_quantity,price_valid_until,reference_market_price',
            'fulfillments.farmer:id,first_name,second_name',
            'fulfillments.items.listing:id,title,unit',
            'payment',
        ])->findOrFail($id);

        $this->authorize('view', $order);

        // Lazily check and expire reservation if past due
        if ($this->reservationService->isExpired($order) && $order->status === 'pending_payment') {
            $this->reservationService->expireReservation($order);
            $order->refresh();
        }

        return response()->json([
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Checkout: convert the buyer's cart or single/bulk produce payload into a new order with
     * concurrency-safe stock reservation and 15-minute expiration timer.
     *
     * POST /api/orders/checkout
     * Body: { "listing_id": 1, "quantity_kg": 1000 } OR { "cart_item_ids": [1, 2] }
     */
    public function checkout(Request $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $itemsToReserve = [];

        // 1. Direct Multi-Item Array Checkout
        if ($request->has('items') && is_array($request->input('items')) && count($request->input('items')) > 0) {
            foreach ($request->input('items') as $reqItem) {
                $listingId = $reqItem['listing_id'] ?? $reqItem['id'] ?? null;
                $quantityKg = max(1, (int) ($reqItem['quantity_kg'] ?? $reqItem['quantity'] ?? 1));
                if ($listingId) {
                    $listing = \App\Models\Listing::find($listingId);
                    if ($listing) {
                        $itemsToReserve[] = [
                            'listing_id'     => $listing->id,
                            'quantity'       => $quantityKg,
                            'price_snapshot' => $listing->price_per_unit,
                        ];
                    }
                }
            }

            if (empty($itemsToReserve)) {
                return response()->json(['message' => 'None of the requested produce listings were found.'], 404);
            }
        }
        // 2. Single Separate Item Checkout
        elseif ($request->has('listing_id')) {
            $listingId  = $request->input('listing_id');
            $quantityKg = max(1, (int) $request->input('quantity_kg', 1000));

            $listing = \App\Models\Listing::find($listingId);
            if (! $listing) {
                return response()->json(['message' => 'Requested produce listing was not found.'], 404);
            }

            $itemsToReserve[] = [
                'listing_id'     => $listing->id,
                'quantity'       => $quantityKg,
                'price_snapshot' => $listing->price_per_unit,
            ];
        }
        // 2. Selective / Bulk Cart Items Checkout
        elseif ($request->has('cart_item_ids') && is_array($request->input('cart_item_ids'))) {
            $cartIds = $request->input('cart_item_ids');
            $cartItems = $user->cartItems()->with('listing')->whereIn('id', $cartIds)->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'No matching cart items found for checkout.'], 422);
            }

            foreach ($cartItems as $cItem) {
                $itemsToReserve[] = [
                    'listing_id'     => $cItem->listing_id,
                    'quantity'       => $cItem->quantity,
                    'price_snapshot' => $cItem->price_snapshot ?: $cItem->listing?->price_per_unit,
                ];
            }

            // Remove selected cart items after reservation
            $user->cartItems()->whereIn('id', $cartIds)->delete();
        }
        // 3. Fallback: All Cart Items
        else {
            $cartItems = $user->cartItems()->with('listing')->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'Your cart is empty.'], 422);
            }

            foreach ($cartItems as $cItem) {
                $itemsToReserve[] = [
                    'listing_id'     => $cItem->listing_id,
                    'quantity'       => $cItem->quantity,
                    'price_snapshot' => $cItem->price_snapshot ?: $cItem->listing?->price_per_unit,
                ];
            }

            $user->cartItems()->delete();
        }

        try {
            $order = $this->reservationService->createReservation($user, $itemsToReserve);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $order->load([
            'items.listing:id,title,unit',
            'fulfillments.farmer:id,first_name,second_name',
            'payment:id,order_id,status',
        ]);

        return response()->json([
            'message' => 'Order placed successfully. Stock reserved for ' . config('marketplace.reservation_duration_minutes', 15) . ' minutes.',
            'order'   => new OrderResource($order),
        ], 201);
    }

    /**
     * Verify the 6-digit delivery handoff PIN.
     *
     * POST /api/orders/{id}/verify-delivery-pin
     * Body: { "pin": "123456" }
     */
    public function verifyDeliveryPin(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'min:4', 'max:6'],
        ]);

        $order = Order::findOrFail($id);

        $this->authorize('view', $order);

        try {
            $this->deliveryService->verifyHandoffPin($order, $request->input('pin'));
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Delivery PIN verified successfully. Order marked as delivered.',
            'order'   => new OrderResource($order->fresh(['fulfillments'])),
        ]);
    }

    /**
     * Cancel a pending-payment order and release reserved stock.
     *
     * POST /api/orders/{id}/cancel
     */
    public function cancel(int $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $this->authorize('cancel', $order);

        $allowedStatuses = ['pending', 'placed', 'pending_farmer_approval', 'pending_payment', 'awaiting_buyer_payment', 'accepted'];
        if (!in_array($order->status, $allowedStatuses)) {
            return response()->json([
                'message' => 'Only pre-paid orders can be cancelled.',
            ], 422);
        }

        $this->reservationService->expireReservation($order);
        
        // Permanently erase the order so it immediately drops off the Farmer's dashboard
        $order->delete();

        return response()->json([
            'message' => 'Order cancelled and permanently deleted.',
        ]);
    }

    /**
     * Admin: List all orders across the entire platform.
     *
     * GET /api/admin/orders?status=pending_payment&search=ORD
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $request->validate([
            'status'   => ['sometimes', 'string'],
            'search'   => ['sometimes', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Order::with([
            'buyer:id,first_name,second_name,phone',
            'items.listing:id,title,unit',
            'fulfillments.farmer:id,first_name,second_name,phone',
            'payment:id,order_id,status,chapa_tx_ref',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function ($b) use ($search) {
                      $b->where('first_name', 'like', "%{$search}%")
                        ->orWhere('second_name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderByDesc('placed_at')->paginate($request->input('per_page', 20));

        return OrderResource::collection($orders)->response();
    }
}


