<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Http\Request;
use App\Support\ActivityLogger;
use App\Notifications\SystemNotification;
use App\Services\FcmSender;

class VisaAssignmentController extends Controller
{
    public function assign(Request $request, VisaApplication $visa)
    {
        $request->validate([
            'consultant_id' => 'required|exists:users,id',
        ]);

        $visa->update([
            'consultant_id' => $request->consultant_id,
        ]);

        ActivityLogger::log(
            'consultant_assigned',
            $visa,
            ['consultant_id' => $request->consultant_id]
        );

        $consultant = User::findOrFail($request->consultant_id);

        // ✅ DATABASE NOTIFICATION (existing)
        $consultant->notify(new SystemNotification(
            type: 'consultant_assigned',
            title: 'New Visa Assigned',
            message: 'A visa application has been assigned to you.',
            data: [
                'visa_id' => $visa->id,
                'screen'  => 'visa_detail',
            ]
        ));

        // ✅ PUSH NOTIFICATION (NEW)
        FcmSender::send(
            user: $consultant,
            title: 'New Visa Assigned',
            body: 'A visa application has been assigned to you.',
            data: [
                'screen'  => 'visa_detail',
                'visa_id' => (string) $visa->id,
            ]
        );

        return back()->with('success', 'Consultant assigned successfully');
    }
}
