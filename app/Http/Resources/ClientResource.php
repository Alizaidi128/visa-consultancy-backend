<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'email' => $this->email,
            'phone' => $this->phone,
            'passport_number' => $this->passport_number,
            'passport_expiry' => optional($this->passport_expiry)->format('Y-m-d'),

            'family_members' => ClientFamilyMemberResource::collection(
                $this->whenLoaded('familyMembers')
            ),

            'visa_count' =>
                $this->visa_applications_count
                ?? $this->whenLoaded(
                    'visaApplications',
                    fn () => $this->visaApplications->count()
                )
                    ?? 0,

        ];
    }
}
