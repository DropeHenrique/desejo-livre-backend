<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reported_content_type',
        'reported_content_id',
        'reported_content_description',
        'reason',
        'description',
        'status',
        'action_taken',
        'admin_notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who made the report.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the user being reported.
     */
    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    /**
     * Get the reason label.
     */
    public function getReasonLabelAttribute(): string
    {
        return match($this->reason) {
            'inappropriate_content' => 'Conteúdo Inadequado',
            'spam' => 'Spam',
            'harassment' => 'Assédio',
            'fake_profile' => 'Perfil Falso',
            'illegal_activity' => 'Atividade Ilegal',
            'copyright' => 'Violação de Copyright',
            'other' => 'Outro',
            default => $this->reason,
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'investigating' => 'Investigando',
            'resolved' => 'Resolvida',
            'dismissed' => 'Descartada',
            default => $this->status,
        };
    }

    /**
     * Scope for pending reports.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for investigating reports.
     */
    public function scopeInvestigating($query)
    {
        return $query->where('status', 'investigating');
    }

    /**
     * Scope for resolved reports.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for dismissed reports.
     */
    public function scopeDismissed($query)
    {
        return $query->where('status', 'dismissed');
    }

    /**
     * Scope for reports by reason.
     */
    public function scopeByReason($query, $reason)
    {
        return $query->where('reason', $reason);
    }
}
