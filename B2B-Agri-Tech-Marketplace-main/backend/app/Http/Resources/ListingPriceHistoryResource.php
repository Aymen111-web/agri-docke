<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingPriceHistoryResource extends JsonResource
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
            'listing_id'     => $this->listing_id,
            'price_per_unit' => $this->price_per_unit,
            'changed_by'     => $this->changed_by,
            'effective_at'   => $this->effective_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,

            // Conditionally loaded relationships
            'listing'         => new ListingResource($this->whenLoaded('listing')),
            'changed_by_user' => new UserResource($this->whenLoaded('changedBy')),
        ];
    }
}
