<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // 🔐 Identity
            'name' => $this->name,
            'email' => $this->email,

            // 📞 Contact
            'phone' => $this->phone,
            'city' => $this->city,
            'address' => $this->address,

            // 🧾 Account state
            'status' => $this->status,
            'email_verified' => (bool) $this->email_verified_at,

            // ✅ GUARD-SAFE ROLE SERIALIZATION
            'roles' => $this->roles->pluck('name')->values(),

            'created_at' => $this->created_at,
        ];
    }
}
