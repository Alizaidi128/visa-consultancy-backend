<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Http\Request;
use App\Support\ActivityLogger;
use App\Notifications\SystemNotification;

class VisaAssignmentApiController extends Controller
{
    public function assign(Request $request, VisaApplication $visa)
    {
        $this->authorize('assign', $visa);

        $request->validate([
            'consultant_id' => ['required', 'exists:users,id'],
        ]);

        $visa->update([
            'consultant_id' => $request->consultant_id,
        ]);

        ActivityLogger::log(
            'consultant_assigned',
            $visa,
            ['consultant_id' => $request->consultant_id]
        );
        $user = User::find($request->consultant_id);

        $user->notify(new SystemNotification(
            type: 'consultant_assigned',
            title: 'New Visa Assigned',
            message: 'A visa application has been assigned to you.',
            data: ['visa_id' => $visa->id]
        ));
        return response()->json([
            'message' => 'Consultant assigned successfully.',
            'visa' => $visa,
        ]);
    }
}
