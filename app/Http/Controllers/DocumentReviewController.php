<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Notifications\SystemNotification;
use App\Services\FcmSender;
use App\Models\Deadline;
use Carbon\Carbon;

class DocumentReviewController extends Controller
{
    public function approve(Document $document)
    {
        $this->authorize('review', $document);

        $document->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
        ]);

        ActivityLog::create([
            'action'        => 'document_approved',
            'loggable_type' => Document::class,
            'loggable_id'   => $document->id,
            'user_id'       => auth()->id(),
            'data'          => ['type' => $document->type],
        ]);

        $clientUser = $document->visaApplication?->client?->user;

        if (! $clientUser) {
            return back()->with('error', 'Client user not found.');
        }

        $clientUser->notify(new SystemNotification(
            type: 'document_approved',
            title: 'Document Approved',
            message: "{$document->type} has been approved.",
            data: [
                'visa_id'     => $document->visa_application_id,
                'document_id' => $document->id,
                'screen'      => 'visa_detail',
            ]
        ));

        FcmSender::send(
            user: $clientUser,
            title: 'Document Approved',
            body: "{$document->type} has been approved.",
            data: [
                'screen'  => 'visa_detail',
                'visa_id' => (string) $document->visa_application_id,
            ]
        );

        return back()->with('success', 'Document approved.');
    }

    public function reject(Request $request, Document $document)
    {
        $this->authorize('review', $document);

        $request->validate([
            'remarks' => ['required', 'string'],
        ]);

        $document->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'review_remarks' => $request->remarks,
        ]);

        ActivityLog::create([
            'action'        => 'document_rejected',
            'loggable_type' => Document::class,
            'loggable_id'   => $document->id,
            'user_id'       => auth()->id(),
            'data'          => ['remarks' => $request->remarks],
        ]);

        $clientUser = $document->visaApplication?->client?->user;

        if (! $clientUser) {
            return back()->with('error', 'Client user not found.');
        }

        $clientUser->notify(new SystemNotification(
            type: 'document_rejected',
            title: 'Document Rejected',
            message: "{$document->type} was rejected because {$request->remarks}.",
            data: [
                'visa_id'          => $document->visa_application_id,
                'document_id'      => $document->id,
                'document_type'    => $document->type,
                'status'           => 'rejected',
                'rejection_reason' => $request->remarks, // ✅ critical
                'screen'           => 'visa_detail',
            ]
        ));

        FcmSender::send(
            user: $clientUser,
            title: 'Document Rejected',
            body: "{$document->type} was rejected because {$request->remarks}.",
            data: [
                'screen'           => 'visa_detail',
                'visa_id'          => (string) $document->visa_application_id,
                'rejection_reason' => $request->remarks,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | ✅ PHASE 6 — CREATE / UPDATE RE-UPLOAD DEADLINE (BY TYPE)
        |--------------------------------------------------------------------------
        */

        $visa = $document->visaApplication;
        $dueDate = Carbon::now()->addDays(7);

        $existingDeadline = Deadline::where('visa_application_id', $visa->id)
            ->where('type', 'document_reupload')
            ->where('document_type', $document->type)
            ->where('status', 'pending')
            ->first();

        if ($existingDeadline) {
            $existingDeadline->update([
                'due_date' => $dueDate,
            ]);
        } else {
            Deadline::create([
                'visa_application_id' => $visa->id,
                'document_type' => strtolower(trim($document->type)),
                'type' => 'document_reupload',
                'title' => "Re-upload required: {$document->type}",
                'due_date' => $dueDate,
                'reminder_offsets' => [3, 1],
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);
        }

        return back()->with('success', 'Document rejected.');
    }
}
