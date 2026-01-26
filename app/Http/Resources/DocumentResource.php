<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->type,
            'status'           => $this->status,
            'rejection_reason' => $this->review_remarks, // ✅ THIS IS WHAT MOBILE NEEDS
            'uploaded_at'      => $this->created_at?->toISOString(),
        ];
    }
}
