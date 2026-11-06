<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCapabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'user_id'                   => $this->user_id,
            'capability_type'           => $this->capability_type,
            'capability_application_id' => $this->capability_application_id,
            'status'                    => $this->status,
            'granted_by'                => $this->granted_by,
            'granted_at'                => $this->granted_at,
            'revoked_at'                => $this->revoked_at,
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at,

            // Conditionally loaded relationships
            'user'        => new UserResource($this->whenLoaded('user')),
            'application' => new CapabilityApplicationResource($this->whenLoaded('application')),
            'granted_by_user' => new UserResource($this->whenLoaded('grantedBy')),
        ];
    }
}
