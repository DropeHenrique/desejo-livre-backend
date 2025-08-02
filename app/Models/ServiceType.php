<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ServiceType extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    // Relacionamentos
    public function companionServices()
    {
        return $this->hasMany(CompanionService::class);
    }

    public function companionProfiles()
    {
        return $this->belongsToMany(CompanionProfile::class, 'companion_services');
    }

    // Scopes
    public function scopePopular($query, $limit = 10)
    {
        return $query->withCount('companionServices')
                    ->orderBy('companion_services_count', 'desc')
                    ->limit($limit);
    }
}
