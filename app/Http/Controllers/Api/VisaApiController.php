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

        $query = VisaApplication::with([
            'client.user',
            'consultant',
            'country',
            'visaType',
            'familyMembers',
        ]);

        if ($user->hasRole('client')) {
            return $user->client
                ? $query->where('client_id', $user->client->id)->get()
                : [];
        }

        if ($user->hasRole('consultant')) {
            return $query->where('consultant_id', $user->id)->get();
        }

        return $query->get(); // admin
    }



    public function show(VisaApplication $visa)
    {
        $this->authorize('view', $visa);

        $visa->load([
            'documents',
            'client.user',
            'consultant',
            'country',
            'visaType',
            'familyMembers', // ✅ LOAD RELATIONSHIP
        ]);

        return response()->json([
            'data' => [
                'id' => $visa->id,
                'status' => $visa->status,
                'client_id' => $visa->client_id,

                'client' => [
                    'id' => $visa->client?->id,
                    'name' => $visa->client?->user?->name,
                ],

                'consultant' => $visa->consultant
                    ? [
                        'id' => $visa->consultant->id,
                        'name' => $visa->consultant->name,
                    ]
                    : null,

                'country' => [
                    'id' => $visa->country_id,
                    'name' => is_string($visa->country)
                        ? $visa->country
                        : $visa->country?->name,
                ],

                'visa_type' => [
                    'id' => $visa->visa_type_id,
                    'name' => is_string($visa->visa_type)
                        ? $visa->visa_type
                        : $visa->visaType?->name,
                ],

                'documents' => $visa->documents->map(fn ($doc) => [
                    'id' => $doc->id,
                    'type' => $doc->type,
                    'original_name' => $doc->original_name,
                    'status' => $doc->status,
                    'uploaded_at' => $doc->created_at,
                    'view_url' => url("/api/v1/documents/{$doc->id}/view"),
                ]),


                'family_members' =>
                    \App\Http\Resources\ClientFamilyMemberResource::collection(
                        $visa->familyMembers
                    ),
            ],
        ]);

    }



    public function store(Request $request)
    {
        $this->authorize('create', VisaApplication::class);

        $data = $request->validate([
            'country_id'   => ['required', 'exists:countries,id'],
            'visa_type_id' => ['required', 'exists:visa_types,id'],
            'client_id'    => ['nullable', 'exists:clients,id'],
        ]);

        $country = \App\Models\Country::findOrFail($data['country_id']);
        $visaType = \App\Models\VisaType::findOrFail($data['visa_type_id']);

        $clientId = auth()->user()->hasRole('admin')
            ? $data['client_id'] ?? abort(422, 'Client is required')
            : auth()->user()->client->id;

        $visa = VisaApplication::create([
            'client_id'    => $clientId,
            'country_id'   => $country->id,
            'visa_type_id' => $visaType->id,
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
