<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientFamilyMemberResource;
use App\Models\Client;
use App\Models\ClientFamilyMember;
use Illuminate\Http\Request;

class ClientFamilyMemberApiController extends Controller
{
    /**
     * List family members
     */
    public function index(Request $request, Client $client)
    {
        $user = $request->user();

        if ($user->hasRole('client')) {
            abort_unless(
                $user->client && $user->client->id === $client->id,
                403
            );
        }

        $members = $client->familyMembers()->latest()->get();

        return ClientFamilyMemberResource::collection($members);
    }

    /**
     * Store (Admin only)
     */
    public function store(Request $request, Client $client)
    {
        $this->authorize('create', ClientFamilyMember::class);

        $validated = app(
            \App\Http\Controllers\Admin\ClientFamilyMemberController::class
        )->validateData($request, $client);

        $member = $client->familyMembers()->create($validated);

        return new ClientFamilyMemberResource($member);
    }

    /**
     * Update (Admin only)
     */
    public function update(Request $request, ClientFamilyMember $familyMember)
    {
        $this->authorize('update', $familyMember);

        $validated = app(
            \App\Http\Controllers\Admin\ClientFamilyMemberController::class
        )->validateData(
            $request,
            $familyMember->client,
            $familyMember->id
        );

        $familyMember->update($validated);

        return new ClientFamilyMemberResource($familyMember);
    }

    /**
     * Delete (Admin only)
     */
    public function destroy(ClientFamilyMember $familyMember)
    {
        $this->authorize('delete', $familyMember);

        $familyMember->delete();

        return response()->json(['success' => true]);
    }
}
