<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;

        return Inertia::render('Client/Dashboard', [
            'title' => 'My Dashboard',
            'visas' => $client
                ? $client->visaApplications()
                    ->with('documents')
                    ->latest()
                    ->get()
                : [],
        ]);
    }


}
