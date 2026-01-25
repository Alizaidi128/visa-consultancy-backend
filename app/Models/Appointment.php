<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    /* ---------------- Status Constants ---------------- */

    public const STATUS_SCHEDULED   = 'scheduled';
    public const STATUS_RESCHEDULED = 'rescheduled';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_CANCELLED   = 'cancelled';
    public const STATUS_NO_SHOW     = 'no_show';

    protected $fillable = [
        'client_id',
        'visa_application_id',
        'scheduled_date',
        'from_time',
        'to_time',
        'status',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'immutable_date:Y-m-d',
    ];

    /* ---------------- Relationships ---------------- */

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function visaApplication()
    {
        return $this->belongsTo(VisaApplication::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ---------------- Domain Logic ---------------- */

    /**
     * Appointment is still actionable
     */
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_SCHEDULED,
            self::STATUS_RESCHEDULED,
        ], true);
    }

    /**
     * Appointment time has fully passed
     */
    public function isPast(): bool
    {
        $end = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $this->scheduled_date->format('Y-m-d') . ' ' . $this->to_time
        );

        return now()->greaterThan($end);
    }

    /**
     * Mark appointment as completed
     */
    public function markCompleted(): void
    {
        if ($this->isActive() && $this->isPast()) {
            $this->update(['status' => self::STATUS_COMPLETED]);
        }
    }

    /* ---------------- Query Scopes ---------------- */

    /**
     * Only scheduled / rescheduled
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SCHEDULED,
            self::STATUS_RESCHEDULED,
        ]);
    }

    /**
     * Visible in UI (hide completed + cancelled)
     */
    public function scopeVisible($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SCHEDULED,
            self::STATUS_RESCHEDULED,
        ]);
    }
}
