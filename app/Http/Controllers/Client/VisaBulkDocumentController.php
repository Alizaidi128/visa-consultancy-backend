<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\VisaDocumentService;

class VisaBulkDocumentController extends Controller
{
    public function store(Request $request, VisaApplication $visa)
    {
        // ✅ POLICY
        $this->authorize('uploadDocument', $visa);

        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['file', 'max:10240'],
        ]);

        // Load visa type + required documents
        $visa->load('visaType.requiredDocuments');

        if (! $visa->visaType) {
            return back()->withErrors([
                'files' => 'Visa type configuration not found.',
            ]);
        }

        /*
        |------------------------------------------------------
        | Normalize required document types
        | lowercase_key => original document name
        |------------------------------------------------------
        */
        $requiredTypes = $visa->visaType->requiredDocuments
            ->mapWithKeys(fn ($doc) => [
                Str::lower($doc->document_type) => $doc->document_type,
            ]);

        $uploaded = [];
        $unmatched = [];

        $service = app(VisaDocumentService::class);

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $lowerName = Str::lower($originalName);

            $matchedKey = $requiredTypes->keys()->first(fn ($type) =>
                Str::contains($lowerName, Str::slug($type, '_')) ||
                Str::contains($lowerName, str_replace(' ', '_', $type)) ||
                Str::contains($lowerName, str_replace(' ', '-', $type))
            );

            if (! $matchedKey) {
                $unmatched[] = ['name' => $originalName];
                continue;
            }

            $matchedType = $requiredTypes[$matchedKey];

            try {
                $document = $service->handleSingleUpload(
                    visa: $visa,
                    file: $file,
                    uploadedBy: auth()->user(),
                    forcedType: $matchedType
                );

                if ($document) {
                    $uploaded[] = [
                        'file' => $originalName,
                        'type' => $matchedType,
                    ];
                }
            } catch (\Throwable $e) {
                $unmatched[] = [
                    'name' => $originalName,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return back()->with([
            'success' => 'Documents uploaded successfully',
            'bulk_uploaded' => $uploaded,
            'bulk_unmatched' => $unmatched,
        ]);
    }
}
