<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Resources\VisaTypeResource;
use App\Http\Resources\RequiredDocumentResource;
use App\Models\Country;
use App\Models\VisaType;
use Illuminate\Http\Request;
use App\Models\VisaApplication;

class ReferenceController extends Controller
{
    /**
     * Get all countries
     */
    public function countries()
    {
        return CountryResource::collection(
            Country::orderBy('name')->get()
        );
    }

    /**
     * Get visa types for a country
     */
    public function visaTypes(Country $country)
    {
        return VisaTypeResource::collection(
            $country->visaTypes()->orderBy('name')->get()
        );
    }

    /**
     * Get required documents for a visa type
     */
    public function requiredDocuments(Request $request, VisaType $visaType)
    {
        $visaId = $request->query('visa_id');

        // If visa_id is NOT provided, fallback to static reference data
        if (!$visaId) {
            return RequiredDocumentResource::collection(
                $visaType->requiredDocuments()->get()
            );
        }

        $visa = VisaApplication::with('documents')
            ->findOrFail($visaId);

        $requiredDocs = $visaType->requiredDocuments()->get();

        $uploadedDocs = $visa->documents
            ->keyBy('type');

        return response()->json([
            'data' => $requiredDocs->map(function ($doc) use ($uploadedDocs) {
                $uploaded = $uploadedDocs->get($doc->document_type);

                return [
                    'id' => $doc->id,
                    'name' => $doc->document_type,
                    'is_mandatory' => $doc->is_mandatory,
                    'status' => $uploaded?->status ?? 'pending',
                    'document_id' => $uploaded?->id,
                    'file_path' => $uploaded?->file_path,
                ];
            }),
        ]);
    }

}
