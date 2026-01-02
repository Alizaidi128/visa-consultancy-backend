<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisaApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,

            'client' => [
                'id' => $this->client?->id,
                'name' => $this->client?->user?->name,
            ],

            'country' => [
                'id' => $this->country_id,
                'name' => $this->country?->name,
            ],

            'visa_type' => [
                'id' => $this->visa_type_id,
                'name' => $this->visaType?->name,
            ],

            'documents' => $this->documents,

            'created_at' => $this->created_at,
        ];
    }

}

