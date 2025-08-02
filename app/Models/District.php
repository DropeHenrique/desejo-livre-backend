<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class District extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'city_id',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    // Relacionamentos
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function companionDistricts()
    {
        return $this->belongsToMany(CompanionProfile::class, 'companion_districts');
    }

    // Scopes
    public function scopeByCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    // Helpers
    public function getFullNameAttribute(): string
    {
        return $this->name . ' - ' . $this->city->name . ' - ' . $this->city->state->uf;
    }
}
