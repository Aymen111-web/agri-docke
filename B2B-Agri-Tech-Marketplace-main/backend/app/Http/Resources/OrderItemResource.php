<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'order_id'             => $this->order_id,
            'order_fulfillment_id' => $this->order_fulfillment_id,
            'listing_id'           => $this->listing_id,
            'quantity'             => $this->quantity,
            'unit_price'           => $this->unit_price,
            'subtotal'             => $this->subtotal,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,

            // Conditionally loaded relationships
            'order'       => new OrderResource($this->whenLoaded('order')),
            'fulfillment' => new OrderFulfillmentResource($this->whenLoaded('fulfillment')),
            'listing'     => new ListingResource($this->whenLoaded('listing')),
        ];
    }
}
