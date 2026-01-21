<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisaApplication;
use App\Models\Deadline;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class VisaDeadlineController extends Controller
{
    /**
     * Store a new deadline
     */
    public function store(Request $request, VisaApplication $visa)
    {
        $this->authorize('manage', Deadline::class);

        $data = $request->validate([
            'type' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'reminder_offsets' => ['nullable', 'array'],
            'reminder_offsets.*' => ['integer'],
            'document_id' => ['nullable', 'exists:documents,id'],
        ]);

        $deadline = $visa->deadlines()->create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        ActivityLog::create([
            'loggable_type' => Deadline::class,
            'loggable_id' => $deadline->id,
            'action' => 'deadline_created',
            'performed_by' => auth()->id(),
            'data' => $data,
        ]);

        return back();
    }

    /**
     * Update an existing deadline
     */
    public function update(Request $request, Deadline $deadline)
    {
        $this->authorize('manage', Deadline::class);

        $data = $request->validate([
            'type' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'date'],
            'reminder_offsets' => ['nullable', 'array'],
            'reminder_offsets.*' => ['integer'],
        ]);

        $deadline->update($data);

        ActivityLog::create([
            'loggable_type' => Deadline::class,
            'loggable_id' => $deadline->id,
            'action' => 'deadline_updated',
            'performed_by' => auth()->id(),
            'data' => $data,
        ]);

        return back();
    }

    /**
     * Mark deadline as completed
     */
    public function complete(Deadline $deadline)
    {
        $this->authorize('manage', Deadline::class);

        $deadline->markCompleted();

        ActivityLog::create([
            'loggable_type' => Deadline::class,
            'loggable_id' => $deadline->id,
            'action' => 'deadline_completed',
            'performed_by' => auth()->id(),
        ]);

        return back();
    }

    /**
     * Delete deadline
     */
    public function destroy(Deadline $deadline)
    {
        $this->authorize('manage', Deadline::class);

        $deadline->delete();

        ActivityLog::create([
            'loggable_type' => Deadline::class,
            'loggable_id' => $deadline->id,
            'action' => 'deadline_deleted',
            'performed_by' => auth()->id(),
        ]);

        return back();
    }
}
