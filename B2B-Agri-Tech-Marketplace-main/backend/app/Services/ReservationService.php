<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReservationService
{
    /**
     * Create a 15-minute stock reservation for a new order.
     *
     * @param  User|int  $buyer
     * @param  iterable|array  $cartItems
     */
    public function createReservation(User|int $buyer, iterable $cartItems): Order
    {
        $buyerId = ($buyer instanceof User) ? $buyer->id : (int) $buyer;

        return DB::transaction(function () use ($buyerId, $cartItems) {
            $totalProduceAmount = 0;

            // Generate cryptographically secure 6-digit handoff PIN
            $deliveryPin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number'           => 'ORD-' . date('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
                'buyer_id'               => $buyerId,
                'status'                 => 'pending_payment',
                'payment_status'         => 'pending',
                'delivery_status'        => 'pending',
                'inspection_status'      => 'pending',
                'payout_status'          => 'locked',
                'produce_amount'         => 0,
                'platform_fee'           => 0,
                'fee_rate'               => 0.0150,
                'total_amount'           => 0,
                'currency'               => 'ETB',
                'delivery_pin'           => $deliveryPin,
                'reservation_expires_at' => now()->addMinutes(config('marketplace.reservation_minutes', 120)),
                'placed_at'              => now(),
            ]);

            $fulfillments = [];

            foreach ($cartItems as $item) {
                // Extract properties flexibly whether $item is an array or CartItem model
                $listingId = is_array($item) ? ($item['listing_id'] ?? null) : ($item->listing_id ?? null);
                $quantity  = is_array($item) ? ($item['quantity'] ?? 0) : ($item->quantity ?? 0);
                
                $unitPrice = is_array($item)
                    ? ($item['price_snapshot'] ?? 0)
                    : ($item->price_snapshot ?? $item->listing?->price_per_unit ?? 0);

                if (! $listingId) {
                    continue;
                }

                // Atomic row locking for concurrency protection
                $listing = Listing::where('id', $listingId)->lockForUpdate()->firstOrFail();

                if ($listing->quantity_available < $quantity) {
                    throw new RuntimeException("Insufficient available stock for listing: {$listing->title}");
                }

                // Reserve inventory
                $listing->decrement('quantity_available', $quantity);
                $listing->increment('quantity_reserved', $quantity);

                $itemSubtotal = $quantity * $unitPrice;
                $totalProduceAmount += $itemSubtotal;

                $farmerId = $listing->farmer_id;
                if (! isset($fulfillments[$farmerId])) {
                    $fulfillments[$farmerId] = $order->fulfillments()->create([
                        'farmer_id'          => $farmerId,
                        'status'             => 'pending',
                        'delivery_status'    => 'pending',
                        'inspection_status'  => 'pending',
                        'payout_status'      => 'locked',
                        'subtotal_amount'    => 0,
                        'produce_amount'     => 0,
                        'platform_fee'       => 0,
                        'farmer_net_payout'  => 0,
                    ]);
                }

                $fulfillment = $fulfillments[$farmerId];
                $fulfillment->increment('subtotal_amount', $itemSubtotal);
                $fulfillment->increment('produce_amount', $itemSubtotal);
                $fulfillment->increment('farmer_net_payout', $itemSubtotal);

                OrderItem::create([
                    'order_id'             => $order->id,
                    'order_fulfillment_id' => $fulfillment->id,
                    'listing_id'           => $listing->id,
                    'quantity'             => $quantity,
                    'unit_price'           => $unitPrice,
                    'subtotal'             => $itemSubtotal,
                ]);
            }

            // Calculate 1.5% Platform Service Fee
            $platformFee = round($totalProduceAmount * 0.0150, 2);
            $totalBuyerAmount = round($totalProduceAmount + $platformFee, 2);

            // Update fulfillment platform fees
            foreach ($fulfillments as $ful) {
                $fulProduce = (float) $ful->fresh()->produce_amount;
                $fulFee = round($fulProduce * 0.0150, 2);
                $ful->update([
                    'platform_fee'      => $fulFee,
                    'farmer_net_payout' => $fulProduce,
                ]);
            }

            $order->update([
                'produce_amount' => $totalProduceAmount,
                'platform_fee'   => $platformFee,
                'total_amount'   => $totalBuyerAmount,
            ]);

            return $order->fresh(['fulfillments.items', 'items']);
        });
    }

    /**
     * Automatically release expired stock reservations back to available inventory.
     */
    public function releaseExpiredReservations(): int
    {
        $expiredOrders = Order::where('status', 'pending_payment')
            ->where('reservation_expires_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, &$count) {
                foreach ($order->items as $item) {
                    $listing = Listing::where('id', $item->listing_id)->lockForUpdate()->first();
                    if ($listing) {
                        $releaseQty = min($item->quantity, $listing->quantity_reserved);
                        $listing->decrement('quantity_reserved', $releaseQty);
                        $listing->increment('quantity_available', $releaseQty);
                    }
                }

                $order->update(['status' => 'expired']);

                foreach ($order->fulfillments as $fulfillment) {
                    $fulfillment->update(['status' => 'cancelled']);
                }

                $count++;
            });
        }

        return $count;
    }

    /**
     * Check if an order reservation has expired.
     */
    public function isExpired(Order $order): bool
    {
        return $order->status === 'pending_payment'
            && $order->reservation_expires_at
            && $order->reservation_expires_at->isPast();
    }

    /**
     * Manually expire an order reservation and release inventory.
     */
    public function expireReservation(Order $order): bool
    {
        if ($order->status !== 'pending_payment') {
            return false;
        }

        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $listing = Listing::where('id', $item->listing_id)->lockForUpdate()->first();
                if ($listing) {
                    $releaseQty = min($item->quantity, $listing->quantity_reserved);
                    $listing->decrement('quantity_reserved', $releaseQty);
                    $listing->increment('quantity_available', $releaseQty);
                }
            }

            $order->update(['status' => 'cancelled']);

            foreach ($order->fulfillments as $fulfillment) {
                $fulfillment->update(['status' => 'cancelled']);
            }

            return true;
        });
    }
}
