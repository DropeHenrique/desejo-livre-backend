<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class BlogCategory extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    // Relacionamentos
    public function posts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_category');
    }

    // Scopes
    public function scopeWithPosts($query)
    {
        return $query->whereHas('posts', function ($q) {
            $q->published();
        });
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->withCount(['posts' => function ($q) {
            $q->published();
        }])->orderBy('posts_count', 'desc')->limit($limit);
    }

    // Helpers
    public function getPostsCountAttribute(): int
    {
        return $this->posts()->published()->count();
    }

    public function getLatestPostAttribute()
    {
        return $this->posts()->published()->latest('published_at')->first();
    }
}
