<?php

namespace App\Services;

use App\Models\OrderFulfillment;
use Illuminate\Support\Facades\DB;

class InspectionService
{
    /**
     * Record produce inspection outcome and determine payout eligibility.
     */
    public function inspectFulfillment(OrderFulfillment $fulfillment, string $inspectionStatus, ?float $acceptedQty = null, ?float $rejectedQty = null, ?string $notes = null): OrderFulfillment
    {
        return DB::transaction(function () use ($fulfillment, $inspectionStatus, $acceptedQty, $rejectedQty, $notes) {
            $payoutStatus = 'locked';

            if ($inspectionStatus === 'accepted') {
                $payoutStatus = 'eligible';
            } elseif ($inspectionStatus === 'partially_accepted') {
                $payoutStatus = 'eligible';
            } elseif ($inspectionStatus === 'rejected') {
                $payoutStatus = 'rejected_refund';
            }

            $fulfillment->update([
                'inspection_status' => $inspectionStatus,
                'payout_status'     => $payoutStatus,
                'accepted_quantity' => $acceptedQty ?? ($inspectionStatus === 'accepted' ? $fulfillment->items->sum('quantity') : 0),
                'rejected_quantity' => $rejectedQty ?? 0,
                'inspection_notes'  => $notes,
                'inspected_at'       => now(),
            ]);

            $order = $fulfillment->order;
            if ($order) {
                $allFulfillments = $order->fulfillments;
                if ($allFulfillments->every(fn ($f) => in_array($f->inspection_status, ['accepted', 'partially_accepted', 'rejected']))) {
                    $order->update([
                        'status'            => 'completed',
                        'inspection_status' => $inspectionStatus,
                        'payout_status'     => $payoutStatus,
                    ]);
                }
            }

            return $fulfillment->fresh();
        });
    }
}
