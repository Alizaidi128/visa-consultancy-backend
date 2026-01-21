<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function store(Request $request, VisaApplication $visa)
    {
        $this->authorize('uploadDocument', $visa);

        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
            'type' => ['required', 'string'],
        ]);

        app(\App\Services\VisaDocumentService::class)->handleSingleUpload(
            visa: $visa,
            file: $request->file('file'),
            uploadedBy: auth()->user(),
            forcedType: $request->type
        );

        return back()->with('success', 'Document uploaded successfully');
    }

}
