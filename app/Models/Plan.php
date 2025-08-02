<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Plan extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_days',
        'user_type',
        'features',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    // Relacionamentos
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function companionProfiles()
    {
        return $this->hasMany(CompanionProfile::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForCompanions($query)
    {
        return $query->where('user_type', 'companion');
    }

    public function scopeForClients($query)
    {
        return $query->where('user_type', 'client');
    }

    // Helpers
    public function isForCompanions(): bool
    {
        return $this->user_type === 'companion';
    }

    public function isForClients(): bool
    {
        return $this->user_type === 'client';
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }
}
