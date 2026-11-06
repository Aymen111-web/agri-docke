<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentWebhookEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'payment_id'         => $this->payment_id,
            'chapa_tx_ref'       => $this->chapa_tx_ref,
            'event_type'         => $this->event_type,
            'chapa_event_id'     => $this->chapa_event_id,
            'payload'            => $this->payload,
            'signature_verified' => $this->signature_verified,
            'processing_status'  => $this->processing_status,
            'processed_at'       => $this->processed_at,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,

            // Conditionally loaded relationships
            'payment' => new PaymentResource($this->whenLoaded('payment')),
        ];
    }
}
