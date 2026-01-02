<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use App\Services\VisaDocumentService;
use Illuminate\Http\Request;

class VisaDocumentApiController extends Controller
{
    public function upload(
        Request $request,
        VisaApplication $visa,
        VisaDocumentService $service
    ) {
        $this->authorize('uploadDocument', $visa);

        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
            'type' => ['required', 'string'],
        ]);

        $document = $service->handleSingleUpload(
            visa: $visa,
            file: $request->file('file'),
            uploadedBy: $request->user(),
            forcedType: $request->type
        );

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'document' => $document,
        ]);
    }

    public function bulkUpload(
        Request $request,
        VisaApplication $visa,
        VisaDocumentService $service
    ) {
        $this->authorize('uploadDocument', $visa);

        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => [
                'file',
                'max:15360',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
            ],
        ]);

        $results = [];
        $unmatched = [];

        foreach ($request->file('files') as $file) {
            try {
                $doc = $service->handleSingleUpload(
                    visa: $visa,
                    file: $file,
                    uploadedBy: $request->user()
                );

                if ($doc) {
                    $results[] = [
                        'file' => $file->getClientOriginalName(),
                        'type' => $doc->type,
                        'status' => 'uploaded',
                    ];
                } else {
                    $unmatched[] = [
                        'file' => $file->getClientOriginalName(),
                        'error' => 'Already approved – skipped',
                    ];
                }
            } catch (\Throwable $e) {
                $unmatched[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Bulk upload completed.',
            'uploaded' => $results,
            'unmatched' => $unmatched,
            'upload_completion_percentage' => $visa->fresh()->upload_completion_percentage,
            'approval_completion_percentage' => $visa->fresh()->completion_percentage,

        ]);
    }
}
