<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $visas = auth()->user()
            ->assignedVisas()
            ->with('client')
            ->latest()
            ->get();

        return Inertia::render('Consultant/Dashboard', [
            'title' => 'Consultant Dashboard',
            'visas' => $visas,
        ]);
    }
}
