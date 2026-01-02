<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisaTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'country_id' => $this->country_id,
            'code'       => $this->code,
            'name'       => $this->name,
        ];
    }
}
