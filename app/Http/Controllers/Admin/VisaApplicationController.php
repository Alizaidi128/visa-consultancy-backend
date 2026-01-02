<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use Inertia\Inertia;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\User;

class VisaApplicationController extends Controller
{
    public function index()
    {
        // ✅ POLICY
        $this->authorize('viewAny', VisaApplication::class);

        $visas = VisaApplication::with('client')
            ->latest()
            ->get();

        return Inertia::render('Admin/Visas/Index', [
            'visas' => $visas,
        ]);
    }

    public function show(VisaApplication $visa)
    {
        $this->authorize('view', $visa);

        $visa->load([
            'client',
            'documents' => fn ($q) => $q->latest(),
            'documents.uploader',
            'consultant',
        ]);

        $consultants = User::role('consultant')
            ->select('id', 'name', 'email')
            ->get();

        $activityLogs = ActivityLog::where(function ($q) use ($visa) {
            $q->where('loggable_type', VisaApplication::class)
                ->where('loggable_id', $visa->id);
        })
            ->orWhere(function ($q) use ($visa) {
                $q->where('loggable_type', Document::class)
                    ->whereIn('loggable_id', $visa->documents()->pluck('id'));
            })
            ->latest()
            ->get();

        return Inertia::render('Admin/Visas/Show', [
            'visa' => $visa,
            'consultants' => $consultants,
            'activityLogs' => $activityLogs,
        ]);
    }
}
