<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        // Shows all clients
        $clients = Client::latest()->paginate(10);

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function create()
    {
        // Shows client creation form
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        // Validates and saves a new client
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'passport_number' => 'nullable|string',
            'passport_expiry' => 'nullable|date',
        ]);

        $validated['created_by'] = auth()->id();

        Client::create($validated);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully');
    }
    public function show(Client $client)
    {
        $client->load([
            'user',
            'visaApplications' => function ($query) {
                $query->latest();
            },
        ]);

        return Inertia::render('Admin/Clients/Show', [
            'client' => $client,
        ]);
    }

}
