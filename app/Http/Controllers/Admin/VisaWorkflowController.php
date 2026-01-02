<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use Illuminate\Http\Request;
use App\Support\ActivityLogger;
use App\Notifications\VisaStatusChanged;
class VisaWorkflowController extends Controller
{
    public function updateStatus(Request $request, VisaApplication $visa)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        try {
            $visa->transitionTo($request->status);
            ActivityLogger::log(
                'visa_status_changed',
                $visa,
                ['new_status' => $request->status]
            );
            $recipient = $visa->consultant ?? auth()->user();

            $recipient->notify(
                new VisaStatusChanged($visa, $request->status)
            );
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }

        return back()->with('success', 'Visa status updated');
    }
}
