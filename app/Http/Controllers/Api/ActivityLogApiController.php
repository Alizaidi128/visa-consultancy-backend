<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogApiController extends Controller
{
    /**
     * Get activity logs for a specific visa
     */
    public function visaLogs(VisaApplication $visa)
    {
        $this->authorize('view', $visa);

        // Get document IDs for this visa
        $documentIds = $visa->documents()->pluck('id');

        $logs = ActivityLog::where(function ($query) use ($visa, $documentIds) {
            $query->where(function ($q) use ($visa) {
                $q->where('loggable_type', VisaApplication::class)
                    ->where('loggable_id', $visa->id);
            })
                ->orWhere(function ($q) use ($documentIds) {
                    $q->where('loggable_type', \App\Models\Document::class)
                        ->whereIn('loggable_id', $documentIds);
                });
        })
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json([
            'data' => $logs,
        ]);
    }


    /**
     * Get activity logs performed by the authenticated user
     */
    public function myLogs(Request $request)
    {
        $logs = ActivityLog::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $logs,
        ]);
    }
}
