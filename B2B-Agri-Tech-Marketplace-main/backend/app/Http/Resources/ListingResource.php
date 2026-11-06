<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $primaryPath = $this->image_path ?: ($this->relationLoaded('images') ? $this->images->first()?->image_path : null);

        return [
            'id'                 => $this->id,
            'farmer_id'          => $this->farmer_id,
            'category_id'        => $this->category_id,
            'title'              => $this->title,
            'description'        => $this->description,
            'image_path'         => $primaryPath,
            'image_url'          => $primaryPath ? (str_starts_with($primaryPath, 'http') ? $primaryPath : asset('storage/' . $primaryPath)) : null,
            'unit'               => $this->unit,
            'price_per_unit'     => $this->price_per_unit,
            'quantity_available' => $this->quantity_available,
            'quantity_reserved'  => $this->quantity_reserved,
            'status'                 => $this->status,
            'batch_number'           => $this->batch_number,
            'harvest_date'           => $this->harvest_date?->format('Y-m-d'),
            'quality_grade'          => $this->quality_grade,
            'region'                 => $this->region,
            'zone'                   => $this->zone,
            'process'                => $this->process,
            'minimum_order_quantity' => $this->minimum_order_quantity,
            'price_valid_from'       => $this->price_valid_from,
            'price_valid_until'      => $this->price_valid_until,
            'reference_market_price' => $this->reference_market_price,
            'created_at'             => $this->created_at,
            'updated_at'         => $this->updated_at,

            // Conditionally loaded relationships
            'images'        => $this->whenLoaded('images', function () {
                return $this->images->map(fn($img) => str_starts_with($img->image_path, 'http') ? $img->image_path : asset('storage/' . $img->image_path));
            }),
            'farmer'        => new UserResource($this->whenLoaded('farmer')),
            'category'      => new CategoryResource($this->whenLoaded('category')),
            'price_history' => ListingPriceHistoryResource::collection($this->whenLoaded('priceHistory')),
        ];
    }
}
