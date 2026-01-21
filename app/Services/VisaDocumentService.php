<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Models\ActivityLog;
use App\Models\Deadline;

class VisaDocumentService
{
    public function handleSingleUpload(
        VisaApplication $visa,
        UploadedFile $file,
        User $uploadedBy,
        ?string $forcedType = null
    ): ?Document {

        $visa->loadMissing('visaType.requiredDocuments');

        if (! $visa->visaType) {
            throw new \Exception('Visa type configuration not found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Determine document type
        |--------------------------------------------------------------------------
        */
        if ($forcedType) {
            $matchedType = $forcedType;
        } else {
            $filename = Str::lower($file->getClientOriginalName());
            $matchedType = null;

            foreach ($visa->visaType->requiredDocuments as $doc) {
                $docType = Str::lower($doc->document_type);

                $cleanDocType = preg_replace('/[^a-z0-9 ]/', '', $docType);
                $cleanFilename = preg_replace('/[^a-z0-9 ]/', '', $filename);

                $keywords = array_filter(
                    explode(' ', $cleanDocType),
                    fn ($w) => strlen($w) >= 3
                );

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
        if (
            Document::where('visa_application_id', $visa->id)
                ->where('type', $matchedType)
                ->where('status', 'approved')
                ->exists()
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove old non-approved docs of same type (versioning)
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
            'loggable_type' => Document::class,
            'loggable_id'   => $document->id,
            'user_id'       => $uploadedBy->id,
            'data'          => [
                'type' => $matchedType,
                'via'  => 'api',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | ✅ PHASE 6 — COMPLETE PENDING RE-UPLOAD DEADLINE (BY TYPE)
        |--------------------------------------------------------------------------
        */
        Deadline::where('visa_application_id', $visa->id)
            ->where('type', 'document_reupload')
            ->where('document_type', strtolower(trim($document->type)))
            ->where('status', 'pending')
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
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
