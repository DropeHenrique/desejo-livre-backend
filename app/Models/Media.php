<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'companion_profile_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'is_primary',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_primary' => 'boolean',
    ];

    // Relacionamentos
    public function companionProfile()
    {
        return $this->belongsTo(CompanionProfile::class);
    }

    // Scopes
    public function scopePhotos($query)
    {
        return $query->where('file_type', 'photo');
    }

    public function scopeVideos($query)
    {
        return $query->where('file_type', 'video');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByCompanion($query, $companionProfileId)
    {
        return $query->where('companion_profile_id', $companionProfileId);
    }

    // Helpers
    public function isPhoto(): bool
    {
        return $this->file_type === 'photo';
    }

    public function isVideo(): bool
    {
        return $this->file_type === 'video';
    }

    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->isPhoto()) {
            $pathInfo = pathinfo($this->file_path);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['basename'];
            return asset('storage/' . $thumbnailPath);
        }

        return $this->url;
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function setAsPrimary(): void
    {
        // Remove primary from other media of this companion
        static::where('companion_profile_id', $this->companion_profile_id)
              ->where('id', '!=', $this->id)
              ->update(['is_primary' => false]);

        // Set this as primary
        $this->update(['is_primary' => true]);
    }
}
