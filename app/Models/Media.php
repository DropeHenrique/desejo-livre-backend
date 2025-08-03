<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'companion_profile_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'duration', // Para vídeos
        'width',    // Para imagens/vídeos
        'height',   // Para imagens/vídeos
        'is_primary',
        'is_verified',
        'is_approved',
        'order',
        'description',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'is_approved' => 'boolean',
        'order' => 'integer',
        'duration' => 'integer', // Duração em segundos
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected $appends = [
        'url',
        'thumbnail_url',
        'formatted_size',
        'formatted_duration',
        'dimensions',
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

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('created_at', 'asc');
    }

    public function scopePublic($query)
    {
        return $query->where('is_approved', true)->where('is_verified', true);
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

    public function isApproved(): bool
    {
        return $this->is_approved;
    }

    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    public function isPublic(): bool
    {
        return $this->is_approved && $this->is_verified;
    }

    public function getUrlAttribute(): string
    {
        if (Storage::disk('public')->exists($this->file_path)) {
            return asset('storage/' . $this->file_path);
        }

        return asset('images/placeholder.jpg');
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->isPhoto()) {
            $pathInfo = pathinfo($this->file_path);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['basename'];

            if (Storage::disk('public')->exists($thumbnailPath)) {
                return asset('storage/' . $thumbnailPath);
            }
        }

        // Para vídeos, retornar thumbnail do vídeo ou placeholder
        if ($this->isVideo()) {
            $pathInfo = pathinfo($this->file_path);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '.jpg';

            if (Storage::disk('public')->exists($thumbnailPath)) {
                return asset('storage/' . $thumbnailPath);
            }
        }

        return asset('images/placeholder.jpg');
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

    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration) return null;

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getDimensionsAttribute(): ?string
    {
        if (!$this->width || !$this->height) return null;

        return $this->width . 'x' . $this->height;
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

    public function approve(): void
    {
        $this->update(['is_approved' => true]);
    }

    public function reject(): void
    {
        $this->update(['is_approved' => false]);
    }

    public function verify(): void
    {
        $this->update(['is_verified' => true]);
    }

    public function unverify(): void
    {
        $this->update(['is_verified' => false]);
    }

    public function moveToPosition(int $newOrder): void
    {
        $oldOrder = $this->order;

        if ($oldOrder < $newOrder) {
            // Moving down: shift items between old and new position up
            static::where('companion_profile_id', $this->companion_profile_id)
                  ->where('order', '>', $oldOrder)
                  ->where('order', '<=', $newOrder)
                  ->decrement('order');
        } else {
            // Moving up: shift items between new and old position down
            static::where('companion_profile_id', $this->companion_profile_id)
                  ->where('order', '>=', $newOrder)
                  ->where('order', '<', $oldOrder)
                  ->increment('order');
        }

        $this->update(['order' => $newOrder]);
    }

    public function deleteFile(): bool
    {
        // Delete main file
        if (Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }

        // Delete thumbnail if exists
        if ($this->isPhoto()) {
            $pathInfo = pathinfo($this->file_path);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['basename'];

            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }
        }

        // Delete video thumbnail if exists
        if ($this->isVideo()) {
            $pathInfo = pathinfo($this->file_path);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '.jpg';

            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }
        }

        return true;
    }

    protected static function boot()
    {
        parent::boot();

        // Auto-set order if not provided
        static::creating(function ($media) {
            if (!$media->order) {
                $maxOrder = static::where('companion_profile_id', $media->companion_profile_id)
                    ->max('order');
                $media->order = ($maxOrder ?? 0) + 1;
            }
        });

        // Delete file when model is deleted
        static::deleted(function ($media) {
            $media->deleteFile();
        });
    }

    // Validation rules
    public static function getValidationRules(): array
    {
        return [
            'file' => 'required|file|max:102400', // 100MB max
            'file_type' => 'required|in:photo,video',
            'description' => 'nullable|string|max:500',
        ];
    }

    public static function getPhotoValidationRules(): array
    {
        return [
            'file' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480', // 20MB max
            'description' => 'nullable|string|max:500',
        ];
    }

    public static function getVideoValidationRules(): array
    {
        return [
            'file' => 'required|mimes:mp4,avi,mov,wmv,flv,webm|max:102400', // 100MB max
            'description' => 'nullable|string|max:500',
        ];
    }
}
