<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use Inertia\Inertia;
use App\Models\ActivityLog;
use App\Models\Document;


class VisaApplicationController extends Controller
{
    public function index()
    {
        // ✅ POLICY
        $this->authorize('viewAny', VisaApplication::class);

        $visas = VisaApplication::with('client')
            ->where('consultant_id', auth()->id())
            ->latest()
            ->get();

        return Inertia::render('Consultant/Visas/Index', [
            'visas' => $visas,
        ]);
    }

    public function show(VisaApplication $visa)
    {
        // ✅ POLICY
        $this->authorize('view', $visa);

        $visa->load([
            'client',
            'documents.uploader',
        ]);

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

        return Inertia::render('Consultant/Visas/Show', [
            'visa' => $visa,
            'activityLogs' => $activityLogs,
        ]);
    }
}
