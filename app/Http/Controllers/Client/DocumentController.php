<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function store(Request $request, VisaApplication $visa)
    {
        // ✅ POLICY: client can upload documents to this visa
        $this->authorize('uploadDocument', $visa);

        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
            'type' => ['required', 'string'],
        ]);

        // Delete old document of same type (single-upload behavior)
        $visa->documents()
            ->where('type', $request->type)
            ->delete();

        $path = $request->file('file')->store(
            "visa_applications/{$visa->id}",
            'public'
        );

        $visa->documents()->create([
            'type' => $request->type,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
            'status' => 'pending',
        ]);

        // Reset visa status
        $visa->update([
            'status' => 'documents_pending',
        ]);

        return back()->with('success', 'Document uploaded successfully');
    }
}
