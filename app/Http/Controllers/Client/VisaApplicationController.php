<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Country;
use App\Models\VisaType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VisaApplicationController extends Controller
{
    /**
     * List client's visa applications
     */
    public function index()
    {
        $client = auth()->user()->client;

        if (! $client) {
            return Inertia::render('Client/Visas/Index', [
                'visas' => [],
            ]);
        }

        $visas = VisaApplication::where('client_id', $client->id)
            ->latest()
            ->get();

        return Inertia::render('Client/Visas/Index', [
            'visas' => $visas,
        ]);
    }

    /**
     * Show a single visa application
     */
    public function show(VisaApplication $visa)
    {
        // ✅ POLICY: replaces abort_unless
        $this->authorize('view', $visa);

        $visa->load([
            'documents',
            'visaType.requiredDocuments',
        ]);

        $requiredDocuments = $visa->visaType
            ? $visa->visaType->requiredDocuments
            : collect();

        // Required for frontend submit logic
        $visa->document_completion_percentage =
            $visa->documentCompletionPercentage();

        $activityLogs = ActivityLog::where(function ($q) use ($visa) {
            $q->where('loggable_type', VisaApplication::class)
                ->where('loggable_id', $visa->id);
        })
            ->orWhere(function ($q) use ($visa) {
                $q->where('loggable_type', Document::class)
                    ->whereIn(
                        'loggable_id',
                        $visa->documents()->pluck('id')
                    );
            })
            ->latest()
            ->get();

        return Inertia::render('Client/Visas/Show', [
            'visa' => $visa,
            'requiredDocuments' => $requiredDocuments,
            'activityLogs' => $activityLogs,
        ]);
    }

    /**
     * Submit visa application
     */
    public function submit(VisaApplication $visa)
    {
        // ✅ POLICY
        $this->authorize('submit', $visa);

        try {
            $visa->transitionTo('submitted');
        } catch (\Exception $e) {
            return back()->withErrors([
                'submit' => $e->getMessage(),
            ]);
        }

        return back();
    }

    /**
     * Create visa application form
     */
    public function create()
    {
        // ✅ POLICY (class-based)
        $this->authorize('create', VisaApplication::class);

        return Inertia::render('Client/Visas/Create', [
            'countries' => Country::with('visaTypes')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store visa application
     */
    public function store(Request $request)
    {
        // ✅ POLICY (class-based)
        $this->authorize('create', VisaApplication::class);

        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'visa_type_id' => ['required', 'exists:visa_types,id'],
        ]);

        $visaType = VisaType::with('country')
            ->findOrFail($validated['visa_type_id']);

        $visa = VisaApplication::create([
            'client_id' => auth()->user()->client->id,

            // Normalized
            'country_id' => $validated['country_id'],
            'visa_type_id' => $validated['visa_type_id'],

            // Legacy (temporary – safe)
            'country' => $visaType->country->name,
            'visa_type' => $visaType->name,

            'status' => 'draft',
        ]);

        ActivityLog::create([
            'loggable_type' => VisaApplication::class,
            'loggable_id' => $visa->id,
            'action' => 'Visa application created',
            'performed_by' => auth()->id(),
        ]);

        return redirect()->route('client.visas.show', $visa);
    }
}
