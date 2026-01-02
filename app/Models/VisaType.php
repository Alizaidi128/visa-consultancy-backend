<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisaType extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'code',
        'name',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function requiredDocuments()
    {
        return $this->belongsToMany(
            RequiredDocument::class,
            'visa_type_required_documents'
        )
            ->withPivot('is_mandatory')
            ->withTimestamps();
    }
}
