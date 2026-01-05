<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientFamilyMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'relationship' => $this->relationship,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'date_of_birth' => optional($this->date_of_birth)->format('Y-m-d'),
            'passport_number' => $this->passport_number,
            'passport_expiry' => optional($this->passport_expiry)->format('Y-m-d'),
        ];
    }
}
