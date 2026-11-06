<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'order_id'           => $this->order_id,
            'chapa_tx_ref'       => $this->chapa_tx_ref,
            'chapa_checkout_url' => $this->chapa_checkout_url,
            'amount'             => $this->amount,
            'currency'           => $this->currency,
            'status'             => $this->status,
            'receipt_url'        => $this->chapa_tx_ref ? "https://chapa.link/payment-receipt/{$this->chapa_tx_ref}" : null,
            'confirmed_at'       => $this->confirmed_at,
            'gateway_metadata'   => $this->gateway_metadata,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,

            // Conditionally loaded relationships
            'order'          => new OrderResource($this->whenLoaded('order')),
            'webhook_events' => PaymentWebhookEventResource::collection($this->whenLoaded('webhookEvents')),
            'exceptions'     => PaymentExceptionResource::collection($this->whenLoaded('exceptions')),
        ];
    }
}
