<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'passport_number',
        'passport_expiry',
        'created_by',
        'user_id',
    ];


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function visaApplications()
    {
        return $this->hasMany(VisaApplication::class);
    }
    public function documents()
    {
        return $this->hasManyThrough(Document::class, VisaApplication::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
