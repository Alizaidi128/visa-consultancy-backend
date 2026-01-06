<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use Inertia\Inertia;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\User;
use App\Models\Client;
use App\Models\Country;
use Illuminate\Http\Request;

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
            'client.familyMembers',   // all client family members
            'familyMembers',          // attached to this visa
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
    public function create()
    {
        $this->authorize('create', VisaApplication::class);

        return Inertia::render('Admin/Visas/Create', [
            'clients' => Client::select('id', 'first_name', 'last_name')->get(),
            'countries' => Country::with('visaTypes')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', VisaApplication::class);

        $data = $request->validate([
            'client_id'    => 'required|exists:clients,id',
            'country_id'   => 'required|exists:countries,id',
            'visa_type_id' => 'required|exists:visa_types,id',
        ]);

        $country = Country::findOrFail($data['country_id']);
        $visaType = \App\Models\VisaType::findOrFail($data['visa_type_id']);

        VisaApplication::create([
            'client_id'    => $data['client_id'],
            'country_id'   => $country->id,
            'visa_type_id' => $visaType->id,
            'country'      => $country->name,
            'visa_type'    => $visaType->name,
            'status'       => 'draft',
        ]);

        return redirect()
            ->route('admin.visa.index')
            ->with('success', 'Visa application created successfully.');
    }

}
