<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentExceptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'payment_id'       => $this->payment_id,
            'order_id'         => $this->order_id,
            'raised_by'        => $this->raised_by,
            'type'             => $this->type,
            'description'      => $this->description,
            'farmer_response'  => $this->farmer_response,
            'status'           => $this->status,
            'resolution_notes' => $this->resolution_notes,
            'resolved_by'      => $this->resolved_by,
            'resolved_at'      => $this->resolved_at,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,

            // Conditionally loaded relationships
            'payment'     => new PaymentResource($this->whenLoaded('payment')),
            'order'       => new OrderResource($this->whenLoaded('order')),
            'raised_user' => new UserResource($this->whenLoaded('raisedBy')),
            'resolved_user' => new UserResource($this->whenLoaded('resolvedBy')),
        ];
    }
}
