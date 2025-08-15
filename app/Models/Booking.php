<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'companion_profile_id',
        'client_user_id',
        'service_type_id',
        'starts_at',
        'duration_minutes',
        'price_total',
        'status',
        'location',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    public function companionProfile()
    {
        return $this->belongsTo(CompanionProfile::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}
