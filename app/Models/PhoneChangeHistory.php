<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneChangeHistory extends Model
{
    use HasFactory;

    protected $table = 'phone_change_history';

    protected $fillable = [
        'companion_profile_id',
        'old_phone',
        'new_phone',
    ];

    // Relacionamentos
    public function companionProfile()
    {
        return $this->belongsTo(CompanionProfile::class);
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
    public function hasOldPhone(): bool
    {
        return !is_null($this->old_phone);
    }

    public function getChangeDescriptionAttribute(): string
    {
        if ($this->hasOldPhone()) {
            return "Telefone alterado de {$this->old_phone} para {$this->new_phone}";
        }

        return "Telefone definido como {$this->new_phone}";
    }
}
