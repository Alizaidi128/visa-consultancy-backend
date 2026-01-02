<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequiredDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function visaTypes()
    {
        return $this->belongsToMany(
            VisaType::class,
            'visa_type_required_documents'
        )
            ->withPivot('is_mandatory')
            ->withTimestamps();
    }
}
