<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use App\Http\Resources\VisaApplicationResource;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class VisaApiController extends Controller
{
    public function myVisas()
    {
        $user = auth()->user();

        if ($user->hasRole('client')) {
            return $user->client
                ? $user->client->visaApplications
                : [];
        }

        if ($user->hasRole('consultant')) {
            return VisaApplication::where('consultant_id', $user->id)->get();
        }

        return VisaApplication::with(['client.user', 'visaType', 'country'])->get();
        // admin
    }


    public function show(VisaApplication $visa)
    {
        $this->authorize('view', $visa);

        return response()->json([
            'data' => $visa->load([
                'documents',
                'client.user',   // ✅ IMPORTANT
                'consultant',
                'country',
                'visaType',
            ]),
        ]);
    }


    public function store(Request $request)
    {
        $this->authorize('create', VisaApplication::class);

        $data = $request->validate([
            'country_id'   => ['required', 'exists:countries,id'],
            'visa_type_id' => ['required', 'exists:visa_types,id'],
        ]);

        $country = \App\Models\Country::findOrFail($data['country_id']);
        $visaType = \App\Models\VisaType::findOrFail($data['visa_type_id']);

        $visa = VisaApplication::create([
            'client_id'    => auth()->user()->client->id,
            'country_id'   => $country->id,
            'visa_type_id' => $visaType->id,

            // ✅ legacy-safe fields
            'country'      => $country->name,
            'visa_type'    => $visaType->name,

            'status'       => 'draft',
        ]);

        return response()->json([
            'message' => 'Visa application created successfully.',
            'visa'    => $visa,
        ], 201);
    }
    public function submit(VisaApplication $visa)
    {
        $this->authorize('submit', $visa);

        if ($visa->status !== 'documents_pending') {
            return response()->json([
                'message' => 'Visa cannot be submitted in current state.'
            ], 422);
        }

        if (! $visa->canBeSubmitted()) {
            return response()->json([
                'message' => 'All required documents must be uploaded and approved before submission.'
            ], 422);
        }

        $visa->update([
            'status' => 'submitted',
            'submission_date' => now(),
        ]);

        ActivityLog::create([
            'action'        => 'visa_submitted',
            'loggable_type' => VisaApplication::class,
            'loggable_id'   => $visa->id,
            'user_id'       => auth()->id(),
            'data'          => [],
        ]);

        return response()->json([
            'message' => 'Visa submitted successfully.',
            'visa' => $visa,
        ]);
    }




}
