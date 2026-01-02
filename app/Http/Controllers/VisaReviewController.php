<?php

namespace App\Http\Controllers;

use App\Models\VisaApplication;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Notifications\SystemNotification;
use App\Services\FcmSender;

class VisaReviewController extends Controller
{
    public function approve(VisaApplication $visa)
    {
        $this->authorize('approve', $visa);

        $visa->transitionTo('approved');

        // ✅ Activity log
        ActivityLog::create([
            'action'        => 'visa_approved',
            'loggable_type' => VisaApplication::class,
            'loggable_id'   => $visa->id,
            'user_id'       => auth()->id(),
            'data'          => [],
        ]);

        $clientUser = $visa->client?->user;

        if ($clientUser) {
            // ✅ DB notification
            $clientUser->notify(new SystemNotification(
                type: 'visa_approved',
                title: 'Visa Approved',
                message: 'Your visa application has been approved.',
                data: [
                    'visa_id' => $visa->id,
                    'screen'  => 'visa_detail',
                ]
            ));

            // ✅ PUSH notification
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

        return back()->with('success', 'Visa approved.');
    }

    public function reject(Request $request, VisaApplication $visa)
    {
        $this->authorize('reject', $visa);

        $request->validate([
            'remarks' => ['required', 'string'],
        ]);

        $visa->update([
            'remarks' => $request->remarks,
        ]);

        $visa->transitionTo('rejected');

        // ✅ Activity log
        ActivityLog::create([
            'action'        => 'visa_rejected',
            'loggable_type' => VisaApplication::class,
            'loggable_id'   => $visa->id,
            'user_id'       => auth()->id(),
            'data'          => ['remarks' => $request->remarks],
        ]);

        $clientUser = $visa->client?->user;

        if ($clientUser) {
            // ✅ DB notification
            $clientUser->notify(new SystemNotification(
                type: 'visa_rejected',
                title: 'Visa Rejected',
                message: 'Your visa application was rejected.',
                data: [
                    'visa_id' => $visa->id,
                    'screen'  => 'visa_detail',
                ]
            ));

            // ✅ PUSH notification
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

        return back()->with('success', 'Visa rejected.');
    }
}
