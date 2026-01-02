<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use Illuminate\Http\Request;

class DocumentApiController extends Controller
{
    public function upload(Request $request, VisaApplication $visa)
    {
        // 🔐 Policy check
        $this->authorize('uploadDocument', $visa);

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:15360', // 15MB
                'mimes:pdf,jpg,jpeg,png,doc,docx',
            ],
        ]);

        /**
         * 🔁 REUSE EXISTING LOGIC
         *
         * You should already have something like:
         * - VisaDocumentService
         * - or logic inside a controller
         *
         * We CALL it here.
         */
        $document = app('App\Services\VisaDocumentService')
            ->handleSingleUpload(
                visa: $visa,
                file: $request->file('file'),
                uploadedBy: $request->user()
            );

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'document' => $document,
            'completion_percentage' => $visa->fresh()->completion_percentage,
        ], 201);
    }
}
