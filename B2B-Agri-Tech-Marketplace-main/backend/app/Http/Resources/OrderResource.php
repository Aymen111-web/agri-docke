<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'order_number'             => $this->order_number,
            'buyer_id'                 => $this->buyer_id,
            'status'                   => $this->status,
            'payment_status'           => $this->payment_status,
            'delivery_status'          => $this->delivery_status,
            'inspection_status'        => $this->inspection_status,
            'payout_status'            => $this->payout_status,
            'produce_amount'           => $this->produce_amount ?? $this->total_amount,
            'platform_fee'             => $this->platform_fee ?? round(($this->total_amount ?? 0) * 0.015, 2),
            'fee_rate'                 => $this->fee_rate ?? 0.0150,
            'total_amount'             => $this->total_amount,
            'currency'                 => $this->currency,
            'placed_at'                => $this->placed_at,
            'reservation_expires_at'   => $this->reservation_expires_at,
            'delivery_pin'             => $this->delivery_pin,
            'delivery_pin_verified_at' => $this->delivery_pin_verified_at,
            'created_at'               => $this->created_at,
            'updated_at'   => $this->updated_at,

            // Conditionally loaded relationships
            'buyer'        => new UserResource($this->whenLoaded('buyer')),
            'items'        => OrderItemResource::collection($this->whenLoaded('items')),
            'fulfillments' => OrderFulfillmentResource::collection($this->whenLoaded('fulfillments')),
            'payment'      => new PaymentResource($this->whenLoaded('payment')),
            'payment_exceptions' => PaymentExceptionResource::collection($this->whenLoaded('paymentExceptions')),
        ];
    }
}
