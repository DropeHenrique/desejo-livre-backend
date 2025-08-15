<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasAddress;
use App\Traits\HasPlanLimitations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasAddress, HasPlanLimitations;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'phone',
        'active',
        'cep',
        'address',
        'complement',
        'state_id',
        'city_id',
        'district_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    // Relacionamentos
    public function companionProfile()
    {
        return $this->hasOne(CompanionProfile::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }

    // Relacionamentos de endereço
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Relacionamento com verificação facial
     */
    public function facialVerification()
    {
        return $this->hasOne(FacialVerification::class);
    }

    // Scopes
    public function scopeClients($query)
    {
        return $query->where('user_type', 'client');
    }

    public function scopeCompanions($query)
    {
        return $query->where('user_type', 'companion');
    }

    public function scopeTransvestites($query)
    {
        return $query->where('user_type', 'transvestite');
    }

    public function scopeMaleEscorts($query)
    {
        return $query->where('user_type', 'male_escort');
    }

    public function scopeAdmins($query)
    {
        return $query->where('user_type', 'admin');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Helpers
    public function isClient(): bool
    {
        return $this->user_type === 'client';
    }

    public function isCompanion(): bool
    {
        return $this->user_type === 'companion';
    }

    public function isTransvestite(): bool
    {
        return $this->user_type === 'transvestite';
    }

    public function isMaleEscort(): bool
    {
        return $this->user_type === 'male_escort';
    }

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isAuthor(): bool
    {
        return $this->isAdmin() || $this->user_type === 'author';
    }

    public function isOnline(): bool
    {
        if ($this->isCompanion() || $this->isTransvestite() || $this->isMaleEscort()) {
            return $this->companionProfile?->online_status ?? false;
        }

        // Para clientes, considerar online se estiver ativo nos últimos 5 minutos
        return $this->last_active_at && $this->last_active_at->diffInMinutes(now()) <= 5;
    }

    public function updateLastActive(): void
    {
        $this->update(['last_active_at' => now()]);
    }
}
