<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class State extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'uf',
        'slug',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    // Relacionamentos
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function companionProfiles()
    {
        return $this->hasManyThrough(CompanionProfile::class, City::class);
    }

    // Scopes
    public function scopeByUf($query, $uf)
    {
        return $query->where('uf', strtoupper($uf));
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}
