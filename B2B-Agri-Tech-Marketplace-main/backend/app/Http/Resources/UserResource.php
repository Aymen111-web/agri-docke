<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'first_name'             => $this->first_name,
            'second_name'            => $this->second_name,
            'phone'                  => $this->phone,
            'phone_verified_at'      => $this->phone_verified_at,
            'is_admin'               => $this->is_admin,
            'account_status'         => $this->account_status,
            'profile_photo_path'     => $this->profile_photo_path,
            'profile_photo_url'      => $this->profile_photo_path ? (str_starts_with($this->profile_photo_path, 'http') ? $this->profile_photo_path : asset('storage/' . $this->profile_photo_path)) : null,
            'businessName'           => $this->company_name,
            'tinNumber'              => $this->tin_number,
            'region'                 => $this->region,
            'deliveryHub'            => $this->delivery_hub,
            'chapa_subaccount_id'    => $this->chapa_subaccount_id,
            'bank_code'              => $this->bank_code,
            'bank_name'              => $this->bank_name,
            'account_name'           => $this->account_name,
            'account_number'         => $this->account_number,
            'account_number_masked'  => $this->account_number ? ('******' . substr($this->account_number, -4)) : null,
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,

            // Conditionally loaded relationships
            'capabilities'             => UserCapabilityResource::collection($this->whenLoaded('capabilities')),
            'capability_applications'  => CapabilityApplicationResource::collection($this->whenLoaded('capabilityApplications')),
        ];
    }
}
