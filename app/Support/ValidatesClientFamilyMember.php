<?php

namespace App\Support;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidatesClientFamilyMember
{
    protected function validateFamilyMember(
        Request $request,
        Client $client,
        ?int $ignoreId = null
    ): array {
        $validated = $request->validate([
            'relationship' => 'required|string|max:50',
            'gender' => 'nullable|in:male,female,other',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'passport_number' => 'nullable|string|max:50',
            'passport_expiry' => 'nullable|date|after_or_equal:today',
        ]);

        // ❗ Spouse gender rule
        if (
            $validated['relationship'] === 'spouse' &&
            $validated['gender'] &&
            $client->gender === $validated['gender']
        ) {
            throw ValidationException::withMessages([
                'gender' => 'Client and spouse cannot have the same gender.',
            ]);
        }

        // ❗ Only one spouse allowed
        if ($validated['relationship'] === 'spouse') {
            $exists = $client->familyMembers()
                ->where('relationship', 'spouse')
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'relationship' => 'A client can have only one spouse.',
                ]);
            }
        }

        return $validated;
    }
}
