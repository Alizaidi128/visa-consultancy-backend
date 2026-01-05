<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'relationship',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'email',
        'phone',
        'passport_number',
        'passport_expiry',
    ];

    protected $casts = [
        'date_of_birth' => 'date:Y-m-d',
        'passport_expiry' => 'date:Y-m-d',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function visaApplications()
    {
        return $this->belongsToMany(
            \App\Models\VisaApplication::class,
            'visa_application_family_member'
        )->withPivot('role')->withTimestamps();
    }

}
