<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deadline extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id',
        'document_id',
        'document_type',
        'type',
        'title',
        'due_date',
        'reminder_offsets',
        'status',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'reminder_offsets' => 'array',
    ];

    /* =====================
     | Relationships
     |=====================*/

    public function visaApplication(): BelongsTo
    {
        return $this->belongsTo(VisaApplication::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* =====================
     | Helpers
     |=====================*/

    public function isOverdue(): bool
    {
        return $this->status === 'pending'
            && $this->due_date->isPast();
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
