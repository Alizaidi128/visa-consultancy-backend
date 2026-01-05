<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->paginate(10);

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Clients/Create');
    }

    public function store(Request $request)
    {
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
                // Check if user exists
                $user = User::where('email', $email)->first();

                if ($user) {
                    // Check if already linked to a client
                    $existingClient = Client::where('user_id', $user->id)->first();

                    if ($existingClient) {
                        throw ValidationException::withMessages([
                            'email' => 'This email already belongs to an existing client.',
                        ]);
                    }
                }
            }

            Client::create([
                ...$validated,
                'created_by' => auth()->id(),
                'user_id'    => null, // linked later if user registers
            ]);

            return back()->with('success', 'Client created successfully.');
        });
    }

    public function show(Client $client)
    {
        $client->load([
            'user',
            'visaApplications' => fn ($q) => $q->latest(),
            'familyMembers'    => fn ($q) => $q->latest(),
        ]);

        return Inertia::render('Admin/Clients/Show', [
            'client' => $client,
        ]);
    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'available',
            ]);
        }

        $client = Client::where('user_id', $user->id)->first();

        return response()->json([
            'status' => $client ? 'client_exists' : 'user_exists',
            'user'   => $user,
            'client' => $client,
        ]);
    }
}
