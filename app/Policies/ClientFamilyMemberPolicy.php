<?php


namespace App\Policies;

use App\Models\ClientFamilyMember;
use App\Models\User;

class ClientFamilyMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'consultant']);
    }

    public function view(User $user, ClientFamilyMember $member): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('consultant')) {
            return true;
        }

        return $user->hasRole('client')
            && $user->client
            && $user->client->id === $member->client_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, ClientFamilyMember $member): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, ClientFamilyMember $member): bool
    {
        return $user->hasRole('admin');
    }
}
