<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanionAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'companion_profile_id',
        'day_of_week',
        'start_time',
        'end_time',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function companionProfile()
    {
        return $this->belongsTo(CompanionProfile::class);
    }
}
