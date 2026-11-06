<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'buyer_id'       => $this->buyer_id,
            'listing_id'     => $this->listing_id,
            'quantity'       => $this->quantity,
            'price_snapshot' => $this->price_snapshot,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,

            // Conditionally loaded relationships
            'buyer'   => new UserResource($this->whenLoaded('buyer')),
            'listing' => new ListingResource($this->whenLoaded('listing')),
        ];
    }
}
