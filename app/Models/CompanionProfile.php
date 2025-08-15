<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Laravel\Scout\Searchable;

class CompanionProfile extends Model
{
    use HasFactory, HasSlug, Searchable;

    protected $fillable = [
        'user_id',
        'artistic_name',
        'slug',
        'age',
        'hide_age',
        'about_me',
        'height',
        'weight',
        'eye_color',
        'hair_color',
        'ethnicity',
        'has_tattoos',
        'has_piercings',
        'has_silicone',
        'is_smoker',
        'verified',
        'verification_date',
        'online_status',
        'last_active',
        'plan_id',
        'plan_expires_at',
        'city_id',
        'attends_home',
        'travel_radius_km',
        'whatsapp',
        'telegram',
    ];

    protected $casts = [
        'hide_age' => 'boolean',
        'has_tattoos' => 'boolean',
        'has_piercings' => 'boolean',
        'has_silicone' => 'boolean',
        'is_smoker' => 'boolean',
        'verified' => 'boolean',
        'online_status' => 'boolean',
        'verification_date' => 'datetime',
        'last_active' => 'datetime',
        'plan_expires_at' => 'datetime',
        'attends_home' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('artistic_name')
            ->saveSlugsTo('slug');
    }

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function services()
    {
        return $this->hasMany(CompanionService::class);
    }

    public function availabilities()
    {
        return $this->hasMany(CompanionAvailability::class);
    }

    public function districts()
    {
        return $this->belongsToMany(District::class, 'companion_districts');
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function photos()
    {
        return $this->hasMany(Media::class)->photos();
    }

    public function videos()
    {
        return $this->hasMany(Media::class)->videos();
    }

    public function primaryPhoto()
    {
        return $this->hasOne(Media::class)->primary()->photos();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function phoneChangeHistory()
    {
        return $this->hasMany(PhoneChangeHistory::class);
    }

    public function cityChangeHistory()
    {
        return $this->hasMany(CityChangeHistory::class);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeOnline($query)
    {
        return $query->where('online_status', true);
    }

    public function scopeByCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeWithActivePlan($query)
    {
        return $query->whereNotNull('plan_id')
                    ->where('plan_expires_at', '>', now());
    }

    public function scopeWithPhotos($query)
    {
        return $query->whereHas('media', function ($q) {
            $q->photos();
        });
    }

    // Scout
    public function toSearchableArray()
    {
        return [
            'artistic_name' => $this->artistic_name,
            'about_me' => $this->about_me,
            'city_name' => $this->city?->name,
            'state_name' => $this->city?->state?->name,
            'age' => $this->hide_age ? null : $this->age,
            'eye_color' => $this->eye_color,
            'hair_color' => $this->hair_color,
            'ethnicity' => $this->ethnicity,
            'verified' => $this->verified,
            'online_status' => $this->online_status,
        ];
    }

    // Helpers
    public function hasActivePlan(): bool
    {
        return $this->plan_id && $this->plan_expires_at && $this->plan_expires_at->isFuture();
    }

    public function averageRating(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function totalReviews(): int
    {
        return $this->reviews()->count();
    }

    public function updateOnlineStatus(): void
    {
        $this->update([
            'online_status' => true,
            'last_active' => now(),
        ]);
    }

    public function markOffline(): void
    {
        $this->update(['online_status' => false]);
    }

    public function getDisplayAgeAttribute(): ?int
    {
        return $this->hide_age ? null : $this->age;
    }

    public function getFormattedHeightAttribute(): ?string
    {
        if (!$this->height) return null;
        return $this->height . ' cm';
    }

    public function getFormattedWeightAttribute(): ?string
    {
        if (!$this->weight) return null;
        return $this->weight . ' kg';
    }

    public function getPrimaryPhotoUrlAttribute(): ?string
    {
        $primaryPhoto = $this->primaryPhoto()->first();
        return $primaryPhoto ? $this->primaryPhoto()->first()->url : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $primaryPhoto = $this->primaryPhoto()->first();
        return $primaryPhoto ? $this->primaryPhoto()->first()->thumbnail_url : null;
    }
}
