<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'companion_id',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // Relacionamentos
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function companion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'companion_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function securityAlerts(): HasMany
    {
        return $this->hasMany(SecurityAlert::class, 'conversation_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('client_id', $userId)
              ->orWhere('companion_id', $userId);
        });
    }

    // Métodos
    public function getOtherParticipant($userId)
    {
        if ($this->client_id === $userId) {
            return $this->companion;
        }
        return $this->client;
    }

    public function isParticipant($userId): bool
    {
        return $this->client_id === $userId || $this->companion_id === $userId;
    }

    public function updateLastMessage()
    {
        $this->update(['last_message_at' => now()]);
    }

    public function getUnreadCount($userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }
}
