<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\VisaApplication;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Notifications\SystemNotification;
use App\Services\FcmSender;

class VisaReviewApiController extends Controller
{
    // -------------------------
    // DOCUMENT REVIEW
    // -------------------------

    public function approveDocument(Document $document)
    {
        $this->authorize('review', $document);

        // ✅ Load relations safely
        $document->load('visaApplication.client.user');

        if (! $document->visaApplication || ! $document->visaApplication->client) {
            return response()->json([
                'message' => 'Visa or client not found for this document.',
            ], 422);
        }

        $document->update([
            'status'       => 'approved',
            'reviewed_by'  => auth()->id(),
            'review_remarks' => null,
        ]);

        ActivityLog::create([
            'action'        => 'document_approved',
            'loggable_type' => Document::class,
            'loggable_id'   => $document->id,
            'user_id'       => auth()->id(),
            'data'          => ['type' => $document->type],
        ]);

        $clientUser = $document->visaApplication->client->user;

        // ✅ DATABASE NOTIFICATION
        if ($clientUser) {
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

            // ✅ PUSH
            FcmSender::send(
                user: $clientUser,
                title: 'Document Approved',
                body: "{$document->type} has been approved.",
                data: [
                    'screen'  => 'visa_detail',
                    'visa_id' => (string) $document->visa_application_id,
                ]
            );
        }

        return response()->json([
            'message'  => 'Document approved.',
            'document' => $document,
        ]);
    }

    public function rejectDocument(Request $request, Document $document)
    {
        $this->authorize('review', $document);

        $request->validate([
            'remarks' => ['required', 'string'],
        ]);

        // ✅ Load relations safely
        $document->load('visaApplication.client.user');

        if (! $document->visaApplication || ! $document->visaApplication->client) {
            return response()->json([
                'message' => 'Visa or client not found for this document.',
            ], 422);
        }

        $document->update([
            'status'          => 'rejected',
            'reviewed_by'     => auth()->id(),
            'review_remarks'  => $request->remarks,
        ]);

        ActivityLog::create([
            'action'        => 'document_rejected',
            'loggable_type' => Document::class,
            'loggable_id'   => $document->id,
            'user_id'       => auth()->id(),
            'data'          => ['remarks' => $request->remarks],
        ]);

        $clientUser = $document->visaApplication->client->user;

        // ✅ DATABASE NOTIFICATION
        if ($clientUser) {
            $clientUser->notify(new SystemNotification(
                type: 'document_rejected',
                title: 'Document Rejected',
                message: "{$document->type} was rejected because {$document->review_remarks}.",
                data: [
                    'visa_id'          => $document->visa_application_id,
                    'document_id'      => $document->id,
                    'document_type'    => $document->type,
                    'status'           => 'rejected',
                    'rejection_reason' => $document->review_remarks, // ✅ ADD
                    'screen'           => 'visa_detail',
                ]
            ));


            // ✅ PUSH
            FcmSender::send(
                user: $clientUser,
                title: 'Document Rejected',
                body: "{$document->type} was rejected because {$document->review_remarks}.",
                data: [
                    'screen'  => 'visa_detail',
                    'visa_id' => (string) $document->visa_application_id,
                ]
            );

        }

        return response()->json([
            'message'  => 'Document rejected.',
            'document' => $document,
        ]);
    }

    // -------------------------
    // VISA DECISION
    // -------------------------

    public function approveVisa(VisaApplication $visa)
    {
        $this->authorize('approve', $visa);

        if ($visa->status !== 'submitted') {
            return response()->json([
                'message' => 'Visa is not ready for approval.'
            ], 422);
        }

        if (! $visa->canBeSubmitted()) {
            return response()->json([
                'message' => 'All mandatory documents must be approved before approving the visa.'
            ], 422);
        }

        $visa->load('client.user');

        $visa->transitionTo('approved');

        ActivityLog::create([
            'action'        => 'visa_approved',
            'loggable_type' => VisaApplication::class,
            'loggable_id'   => $visa->id,
            'user_id'       => auth()->id(),
            'data'          => [],
        ]);

        $clientUser = $visa->client->user;

        if ($clientUser) {
            $clientUser->notify(new SystemNotification(
                type: 'visa_approved',
                title: 'Visa Approved',
                message: 'Your visa application has been approved.',
                data: [
                    'visa_id' => $visa->id,
                    'screen'  => 'visa_detail',
                ]
            ));

            FcmSender::send(
                user: $clientUser,
                title: 'Visa Approved',
                body: 'Your visa application has been approved.',
                data: [
                    'screen'  => 'visa_detail',
                    'visa_id' => (string) $visa->id,
                ]
            );
        }

        return response()->json([
            'message' => 'Visa approved.',
            'visa'    => $visa,
        ]);
    }

    public function rejectVisa(Request $request, VisaApplication $visa)
    {
        $this->authorize('reject', $visa);

        $request->validate([
            'remarks' => ['required', 'string'],
        ]);

        $visa->load('client.user');

        $visa->update([
            'remarks' => $request->remarks,
        ]);

        $visa->transitionTo('rejected');

        $clientUser = $visa->client->user;

        if ($clientUser) {
            $clientUser->notify(new SystemNotification(
                type: 'visa_rejected',
                title: 'Visa Rejected',
                message: 'Your visa application was rejected.',
                data: [
                    'visa_id' => $visa->id,
                    'screen'  => 'visa_detail',
                ]
            ));

            FcmSender::send(
                user: $clientUser,
                title: 'Visa Rejected',
                body: 'Your visa application was rejected.',
                data: [
                    'screen'  => 'visa_detail',
                    'visa_id' => (string) $visa->id,
                ]
            );
        }

        return response()->json([
            'message' => 'Visa rejected.',
            'visa'    => $visa,
        ]);
    }
}
