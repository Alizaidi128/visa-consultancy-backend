<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use App\Models\Document;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

            // Skip if approved document already exists
            $approvedExists = Document::where('visa_application_id', $visa->id)
                ->where('type', $matchedType)
                ->where('status', 'approved')
                ->exists();

            if ($approvedExists) {
                continue;
            }

            // Remove previous non-approved documents
            Document::where('visa_application_id', $visa->id)
                ->where('type', $matchedType)
                ->where('status', '!=', 'approved')
                ->delete();

            $path = $file->store(
                "visa_applications/{$visa->id}",
                'public'
            );

            $document = Document::create([
                'visa_application_id' => $visa->id,
                'type' => $matchedType,
                'original_name' => $originalName,
                'file_path' => $path,
                'uploaded_by' => auth()->id(),
                'status' => 'pending',
            ]);

            ActivityLog::create([
                'loggable_type' => Document::class,
                'loggable_id' => $document->id,
                'action' => 'document_uploaded',
                'performed_by' => auth()->id(),
                'data' => json_encode([
                    'type' => $matchedType,
                    'bulk' => true,
                ]),
            ]);

            $uploaded[] = [
                'file' => $originalName,
                'type' => $matchedType,
            ];
        }

        if (! empty($uploaded)) {
            $visa->update([
                'status' => 'documents_pending',
            ]);
        }

        return back()->with([
            'success' => 'Documents uploaded successfully',
            'bulk_uploaded' => $uploaded,
            'bulk_unmatched' => $unmatched,
        ]);
    }
}
