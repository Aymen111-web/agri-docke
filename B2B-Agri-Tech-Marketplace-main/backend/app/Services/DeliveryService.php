<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DeliveryService
{
    /**
     * Verify the 6-digit delivery handoff PIN.
     */
    public function verifyHandoffPin(Order $order, string $pin): bool
    {
        if ($order->delivery_pin !== $pin) {
            return false;
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status'                   => \App\Models\Order::STATUS_COMPLETED,
                'delivery_status'          => 'delivered',
                'delivery_pin_verified_at' => now(),
            ]);

            foreach ($order->fulfillments as $fulfillment) {
                // Deduct final reserved stock quantities natively
                foreach ($fulfillment->items as $item) {
                    $listing = \App\Models\Listing::where('id', $item->listing_id)->lockForUpdate()->first();
                    if ($listing) {
                        $listing->decrement('quantity_reserved', (float) $item->quantity);
                    }
                }

                $fulfillment->update([
                    'status'            => 'completed',
                    'delivery_status'   => 'delivered',
                    'inspection_status' => 'approved',
                    'completed_at'      => now(),
                ]);
            }
        });

        return true;
    }
}
