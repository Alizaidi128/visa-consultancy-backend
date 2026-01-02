<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\VisaType;
use App\Models\RequiredDocument;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VisaTypeDocumentController extends Controller
{
    /**
     * Show mapping screen
     */
    public function index()
    {
        return Inertia::render('Admin/VisaDocuments/Index', [
            'countries' => Country::orderBy('name')->get(),
            'documents' => RequiredDocument::orderBy('document_type')->get(),
        ]);
    }

    /**
     * Fetch visa types for selected country
     */
    public function getVisaTypes(Country $country)
    {
        return response()->json(
            $country->visaTypes()->orderBy('name')->get()
        );
    }

    /**
     * Fetch mapped documents for visa type
     */
    public function getMappedDocuments(VisaType $visaType)
    {
        return response()->json(
            $visaType->requiredDocuments->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'is_mandatory' => (bool) $doc->pivot->is_mandatory,
                ];
            })
        );
    }

    /**
     * Store / update document mapping
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'visa_type_id' => ['required', 'exists:visa_types,id'],
            'documents' => ['array'],
            'documents.*.id' => ['required', 'exists:required_documents,id'],
            'documents.*.selected' => ['boolean'],
            'documents.*.is_mandatory' => ['boolean'],
        ]);

        $visaType = VisaType::findOrFail($validated['visa_type_id']);

        /**
         * ✅ Build sync data ONLY from selected documents
         * ❌ Unselected docs are automatically removed
         */
        $syncData = collect($validated['documents'] ?? [])
            ->filter(fn ($doc) => $doc['selected'] === true)
            ->mapWithKeys(fn ($doc) => [
                $doc['id'] => [
                    'is_mandatory' => $doc['is_mandatory'] ?? false,
                ],
            ])
            ->toArray();

        /**
         * 🔁 Replace mapping (single active mapping rule)
         */
        $visaType->requiredDocuments()->sync($syncData);

        return back()->with('success', 'Required documents mapping saved successfully.');
    }
}
