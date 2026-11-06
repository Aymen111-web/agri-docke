<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderFulfillmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'order_id'          => $this->order_id,
            'farmer_id'         => $this->farmer_id,
            'status'            => $this->status,
            'delivery_status'   => $this->delivery_status,
            'inspection_status' => $this->inspection_status,
            'payout_status'     => $this->payout_status,
            'subtotal_amount'   => $this->subtotal_amount,
            'produce_amount'    => $this->produce_amount ?? $this->subtotal_amount,
            'platform_fee'      => $this->platform_fee ?? round(($this->subtotal_amount ?? 0) * 0.015, 2),
            'farmer_net_payout' => $this->farmer_net_payout ?? $this->subtotal_amount,
            'accepted_quantity' => $this->accepted_quantity,
            'rejected_quantity' => $this->rejected_quantity,
            'inspection_notes'  => $this->inspection_notes,
            'farmer_notes'      => $this->farmer_notes,
            'accepted_at'       => $this->accepted_at,
            'rejected_at'       => $this->rejected_at,
            'inspected_at'      => $this->inspected_at,
            'completed_at'      => $this->completed_at,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,

            // Conditionally loaded relationships
            'order'  => new OrderResource($this->whenLoaded('order')),
            'farmer' => new UserResource($this->whenLoaded('farmer')),
            'items'  => OrderItemResource::collection($this->whenLoaded('items')),
            'payout' => new PayoutResource($this->whenLoaded('payout')),
        ];
    }
}
