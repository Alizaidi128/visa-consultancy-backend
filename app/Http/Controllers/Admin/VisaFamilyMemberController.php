<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use App\Models\ClientFamilyMember;
use Illuminate\Http\Request;

class VisaFamilyMemberController extends Controller
{
    public function store(Request $request, VisaApplication $visa)
    {
        $data = $request->validate([
            'family_member_ids' => ['required', 'array'],
            'family_member_ids.*' => [
                'exists:client_family_members,id',
            ],
        ]);

        // Ensure family member belongs to same client
        $validIds = ClientFamilyMember::where('client_id', $visa->client_id)
            ->whereIn('id', $data['family_member_ids'])
            ->pluck('id');

        $visa->familyMembers()->syncWithoutDetaching(
            $validIds->mapWithKeys(fn ($id) => [
                $id => ['role' => 'dependent'],
            ])
        );

        return back()->with('success', 'Family members attached to visa.');
    }

    public function destroy(
        VisaApplication $visa,
        ClientFamilyMember $familyMember
    ) {
        $visa->familyMembers()->detach($familyMember->id);

        return back()->with('success', 'Family member removed from visa.');
    }
}
