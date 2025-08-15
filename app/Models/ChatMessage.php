<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'message_type',
        'metadata',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Relacionamentos
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('message_type', $type);
    }

    // Métodos
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function isFromUser($userId): bool
    {
        return $this->sender_id === $userId;
    }

    public function getFormattedTime(): string
    {
        return $this->created_at->format('H:i');
    }

    public function getFormattedDate(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    // Métodos para diferentes tipos de mensagem
    public function isServiceRequest(): bool
    {
        return $this->message_type === 'service_request';
    }

    public function isSecurityAlert(): bool
    {
        return $this->message_type === 'security_alert';
    }

    public function isText(): bool
    {
        return $this->message_type === 'text';
    }

    public function getServiceData()
    {
        if ($this->isServiceRequest() && isset($this->metadata['service'])) {
            return $this->metadata['service'];
        }
        return null;
    }

    public function getAlertData()
    {
        if ($this->isSecurityAlert() && isset($this->metadata['alert'])) {
            return $this->metadata['alert'];
        }
        return null;
    }
}
