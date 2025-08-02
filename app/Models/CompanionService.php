<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanionService extends Model
{
    use HasFactory;

    protected $fillable = [
        'companion_profile_id',
        'service_type_id',
        'price',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relacionamentos
    public function companionProfile()
    {
        return $this->belongsTo(CompanionProfile::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    // Scopes
    public function scopeWithPrice($query)
    {
        return $query->whereNotNull('price');
    }

    public function scopeByServiceType($query, $serviceTypeId)
    {
        return $query->where('service_type_id', $serviceTypeId);
    }

    public function scopeByCompanion($query, $companionProfileId)
    {
        return $query->where('companion_profile_id', $companionProfileId);
    }

    // Helpers
    public function hasPrice(): bool
    {
        return !is_null($this->price);
    }

    public function getFormattedPriceAttribute(): string
    {
        if (!$this->hasPrice()) {
            return 'A combinar';
        }

        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }
}
