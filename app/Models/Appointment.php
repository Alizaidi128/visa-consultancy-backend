<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
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

    public function isActive(): bool
    {
        return in_array($this->status, ['scheduled', 'rescheduled']);
    }
}
