<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\VisaApplication;
use App\Models\Document;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => [
                'clients'       => Client::count(),
                'visas'         => VisaApplication::count(),
                'pendingDocs'   => Document::where('status', 'pending')->count(),
                'consultants'   => User::role('consultant')->count(),
            ],
        ]);
    }
}
