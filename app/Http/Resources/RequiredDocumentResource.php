<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequiredDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->document_type,
            'rejection_reason' => $this->review_remarks,
            'is_mandatory' => (bool) $this->pivot->is_mandatory,



        ];
    }
}
