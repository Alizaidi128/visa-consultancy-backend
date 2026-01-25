<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Deadline;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id',
        'type',
        'original_name',
        'file_path',
        'uploaded_by',
        'status',
        'reviewed_by',
        'review_remarks',
    ];

    // ✅ ADD THIS
    protected $appends = ['rejection_reason'];

    // ✅ ADD THIS
    public function getRejectionReasonAttribute()
    {
        return $this->review_remarks;
    }

    public function visaApplication()
    {
        return $this->belongsTo(VisaApplication::class, 'visa_application_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function deadlines(): HasMany
    {
        return $this->hasMany(Deadline::class);
    }
}
