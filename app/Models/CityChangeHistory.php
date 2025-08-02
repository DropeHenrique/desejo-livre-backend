<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityChangeHistory extends Model
{
    use HasFactory;

    protected $table = 'city_change_history';

    protected $fillable = [
        'companion_profile_id',
        'old_city_id',
        'new_city_id',
    ];

    // Relacionamentos
    public function companionProfile()
    {
        return $this->belongsTo(CompanionProfile::class);
    }

    public function oldCity()
    {
        return $this->belongsTo(City::class, 'old_city_id');
    }

    public function newCity()
    {
        return $this->belongsTo(City::class, 'new_city_id');
    }

    // Scopes
    public function scopeByCompanion($query, $companionProfileId)
    {
        return $query->where('companion_profile_id', $companionProfileId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helpers
    public function hasOldCity(): bool
    {
        return !is_null($this->old_city_id);
    }

    public function getChangeDescriptionAttribute(): string
    {
        if ($this->hasOldCity()) {
            $oldCityName = $this->oldCity?->name ?? 'Cidade desconhecida';
            $newCityName = $this->newCity?->name ?? 'Cidade desconhecida';
            return "Cidade alterada de {$oldCityName} para {$newCityName}";
        }

        $newCityName = $this->newCity?->name ?? 'Cidade desconhecida';
        return "Cidade definida como {$newCityName}";
    }
}
