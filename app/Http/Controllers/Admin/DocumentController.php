<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\VisaApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\ActivityLogger;

class DocumentController extends Controller
{
    public function store(Request $request, VisaApplication $visa)
    {
        $existing = $visa->documents()
            ->where('type', $request->type)
            ->where('status', 'approved')
            ->first();

        if ($existing) {
            return back()->withErrors('Approved document cannot be replaced.');
        }

        $request->validate([
            'document' => 'required|file|max:5120',
            'type' => 'required|string',
        ]);

        $file = $request->file('document');

        $path = $file->store(
            "visa_applications/{$visa->id}",
            'public'
        );

        Document::create([
            'visa_application_id' => $visa->id,
            'type' => $request->type,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
        ]);


        ActivityLogger::log(
            'document_uploaded',
            $visa,
            [
                'type' => $request->type,
                'file' => $file->getClientOriginalName(),
            ]
        );


        return back()->with('success', 'Document uploaded successfully');
    }
}
