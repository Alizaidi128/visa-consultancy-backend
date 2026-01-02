<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Models\ActivityLog;

class VisaDocumentService
{
    public function handleSingleUpload(
        VisaApplication $visa,
        UploadedFile $file,
        User $uploadedBy,
        ?string $forcedType = null
    ): ?Document {

        // Load visa type & required documents
        $visa->loadMissing('visaType.requiredDocuments');

        if (! $visa->visaType) {
            throw new \Exception('Visa type configuration not found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Determine document type
        |--------------------------------------------------------------------------
        */

        // ✅ SINGLE UPLOAD: trust provided type (NO filename matching)
        if ($forcedType) {
            $matchedType = $forcedType;
        }

        // ✅ BULK UPLOAD: match filename to required documents
        else {
            $filename = Str::lower($file->getClientOriginalName());
            $matchedType = null;

            $filename = Str::lower($file->getClientOriginalName());

            foreach ($visa->visaType->requiredDocuments as $doc) {
                $docType = Str::lower($doc->document_type);

                // Normalize both sides
                $cleanDocType = preg_replace('/[^a-z0-9 ]/', '', $docType);
                $cleanFilename = preg_replace('/[^a-z0-9 ]/', '', $filename);

                // Split document type into keywords
                $keywords = array_filter(explode(' ', $cleanDocType), function ($word) {
                    return strlen($word) >= 3; // ignore "of", "to", etc.
                });

                foreach ($keywords as $word) {
                    if (Str::contains($cleanFilename, $word)) {
                        $matchedType = $doc->document_type;
                        break 2;
                    }
                }
            }


            if (! $matchedType) {
                throw new \Exception('Unable to match document type.');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent overwrite of approved documents
        |--------------------------------------------------------------------------
        */
        $approvedExists = Document::where('visa_application_id', $visa->id)
            ->where('type', $matchedType)
            ->where('status', 'approved')
            ->exists();

        if ($approvedExists) {
           // throw new \Exception("Approved document already exists for {$matchedType}");
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove old non-approved docs of same type
        |--------------------------------------------------------------------------
        */
        Document::where('visa_application_id', $visa->id)
            ->where('type', $matchedType)
            ->where('status', '!=', 'approved')
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Store file
        |--------------------------------------------------------------------------
        */
        $path = $file->store(
            "visa_applications/{$visa->id}",
            'public'
        );

        /*
        |--------------------------------------------------------------------------
        | Save document
        |--------------------------------------------------------------------------
        */
        $document = Document::create([
            'visa_application_id' => $visa->id,
            'type' => $matchedType,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => $uploadedBy->id,
            'status' => 'pending',
        ]);
        ActivityLog::create([
            'action'        => 'document_uploaded',
            'loggable_type' => \App\Models\Document::class,
            'loggable_id'   => $document->id,
            'user_id'       => $uploadedBy->id,
            'data'          => [
                'type' => $matchedType,
                'via'  => 'api',
            ],
        ]);
        /*
        |--------------------------------------------------------------------------
        | Reset visa status
        |--------------------------------------------------------------------------
        */
        $visa->update([
            'status' => 'documents_pending',
        ]);

        return $document;
    }
}
