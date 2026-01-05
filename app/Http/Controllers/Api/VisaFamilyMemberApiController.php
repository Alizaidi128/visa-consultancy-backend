<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientFamilyMember;
use App\Models\VisaApplication;
use Illuminate\Http\Request;

class VisaFamilyMemberApiController extends Controller
{
    public function store(Request $request, VisaApplication $visa)
    {
        $request->validate([
            'family_member_ids' => ['required', 'array'],
            'family_member_ids.*' => ['exists:client_family_members,id'],
        ]);

        $validIds = ClientFamilyMember::where('client_id', $visa->client_id)
            ->whereIn('id', $request->family_member_ids)
            ->pluck('id');

        $visa->familyMembers()->syncWithoutDetaching(
            $validIds->mapWithKeys(fn ($id) => [
                $id => ['role' => 'dependent'],
            ])
        );

        return response()->json(['success' => true]);
    }

    public function destroy(
        VisaApplication $visa,
        ClientFamilyMember $familyMember
    ) {
        $visa->familyMembers()->detach($familyMember->id);

        return response()->json(['success' => true]);
    }
}
