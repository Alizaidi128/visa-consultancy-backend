<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;

class ClientPolicy
{
    public function update(User $user, Client $client)
    {
        return $user->hasRole(['admin', 'consultant']);
    }
    public function delete(User $user, Client $client)
    {
        return $user->hasRole(['admin', 'consultant']);
    }


    public function view(User $user, Client $client)
    {
        return $user->hasRole(['admin', 'consultant']) ||
            $user->id === $client->user_id;
    }

    public function create(User $user)
    {
        return $user->hasRole(['admin', 'consultant']);
    }
}
