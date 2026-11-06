<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'farmer_id'             => $this->farmer_id,
            'order_fulfillment_id'  => $this->order_fulfillment_id,
            'amount'                => $this->amount,
            'status'                => $this->status,
            'reference'             => $this->reference,
            'processed_at'          => $this->processed_at,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,

            // Conditionally loaded relationships
            'farmer'      => new UserResource($this->whenLoaded('farmer')),
            'fulfillment' => new OrderFulfillmentResource($this->whenLoaded('fulfillment')),
        ];
    }
}
