<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CapabilityApplicationResource extends JsonResource
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
            'user_id'               => $this->user_id,
            'capability_type'       => $this->capability_type,
            'status'                => $this->status,
            'supporting_documents'  => $this->supporting_documents,
            'rejection_reason'      => $this->rejection_reason,
            'reviewed_by'           => $this->reviewed_by,
            'reviewed_at'           => $this->reviewed_at,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,

            // Conditionally loaded relationships
            'user'             => new UserResource($this->whenLoaded('user')),
            'reviewer'         => new UserResource($this->whenLoaded('reviewer')),
            'capability_grant' => new UserCapabilityResource($this->whenLoaded('capabilityGrant')),
        ];
    }
}
