<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientFamilyMember;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClientFamilyMemberController extends Controller
{
    /* -------------------------------------------------------
       STORE
    ------------------------------------------------------- */
    public function store(Request $request, Client $client)
    {
        $validated = $this->validateData($request, $client);

        $client->familyMembers()->create($validated);

        return back()->with('success', 'Family member added successfully.');
    }

    /* -------------------------------------------------------
       UPDATE
    ------------------------------------------------------- */
    public function update(Request $request, ClientFamilyMember $familyMember)
    {
        $validated = $this->validateData(
            $request,
            $familyMember->client,
            $familyMember->id
        );

        $familyMember->update($validated);

        return back()->with('success', 'Family member updated.');
    }

    /* -------------------------------------------------------
       DELETE
    ------------------------------------------------------- */
    public function destroy(ClientFamilyMember $familyMember)
    {
        $familyMember->delete();

        return back()->with('success', 'Family member deleted.');
    }

    /* -------------------------------------------------------
       SHARED VALIDATION (CREATE + UPDATE)
    ------------------------------------------------------- */
    protected function validateData(Request $request, Client $client, $ignoreId = null): array
    {
        $validated = $request->validate([
            'relationship' => 'required|string|max:50',
            'gender' => 'nullable|in:male,female,other',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'passport_number' => 'nullable|string|max:50',
            'passport_expiry' => 'nullable|date|after_or_equal:today',
        ]);

        /* -------------------------------
           SPOUSE GENDER CROSS-CHECK
        -------------------------------- */
        if (
            $validated['relationship'] === 'spouse' &&
            $validated['gender'] &&
            $client->gender === $validated['gender']
        ) {
            throw ValidationException::withMessages([
                'gender' => 'Client and spouse cannot have the same gender.',
            ]);
        }

        /* -------------------------------
           ONLY ONE SPOUSE ALLOWED
        -------------------------------- */
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
