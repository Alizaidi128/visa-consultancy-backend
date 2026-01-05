<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientApiController extends Controller
{
    /**
     * List clients (Admin / Consultant)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless(
            $user->hasAnyRole(['admin', 'consultant']),
            403
        );

        $clients = Client::latest()->paginate(10);

        return ClientResource::collection($clients);
    }

    /**
     * Show single client
     * - Admin / Consultant: any client
     * - Client: only own profile
     */
    public function show(Request $request, Client $client)
    {
        $user = $request->user();

        if ($user->hasRole('client')) {
            abort_unless(
                $user->client && $user->client->id === $client->id,
                403
            );
        }

        $client->load([
            'familyMembers' => fn ($q) => $q->latest(),
            'visaApplications',
        ]);

        return new ClientResource($client);
    }

    /**
     * Create client (Admin + Consultant)
     */
    public function store(Request $request)
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin', 'consultant']),
            403
        );

        $validated = $request->validate([
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'gender'            => 'nullable|in:male,female,other',
            'email'             => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:30',
            'passport_number'   => 'nullable|string|max:50',
            'passport_expiry'   => 'nullable|date|after_or_equal:today',
        ]);

        return DB::transaction(function () use ($validated) {

            $email = $validated['email'] ?? null;

            if ($email) {
                $user = User::where('email', $email)->first();

                if ($user) {
                    $existingClient = Client::where('user_id', $user->id)->first();

                    if ($existingClient) {
                        throw ValidationException::withMessages([
                            'email' => 'This email already belongs to an existing client.',
                        ]);
                    }
                }
            }

            $client = Client::create([
                ...$validated,
                'created_by' => auth()->id(),
                'user_id'    => null,
            ]);

            return new ClientResource($client);
        });
    }
}
