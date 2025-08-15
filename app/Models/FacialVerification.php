<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacialVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'face_encoding',
        'document_front_path',
        'document_back_path',
        'face_photo_path',
        'document_with_face_path',
        'status',
        'rejection_reason',
        'verified_at',
        'last_face_login_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'last_face_login_at' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para verificações aprovadas
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope para verificações pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para verificações rejeitadas
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Verifica se a verificação está aprovada
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Verifica se a verificação está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verifica se a verificação foi rejeitada
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Marca a verificação como aprovada
     */
    public function approve(): void
    {
        $this->update([
            'status' => 'approved',
            'verified_at' => now(),
        ]);
    }

    /**
     * Marca a verificação como rejeitada
     */
    public function reject(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Registra login facial
     */
    public function recordFaceLogin(): void
    {
        $this->update([
            'last_face_login_at' => now(),
        ]);
    }
}
