<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'triggered_by',
        'alert_type',
        'triggered_content',
        'description',
        'severity',
        'is_resolved',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    // Relacionamentos
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    // Scopes
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    // Métodos
    public function resolve()
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);
    }

    public function isHighSeverity(): bool
    {
        return in_array($this->severity, ['high', 'critical']);
    }

    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    // Constantes para tipos de alerta
    const TYPE_PHONE_REQUEST = 'phone_request';
    const TYPE_PERSONAL_INFO = 'personal_info';
    const TYPE_EXTERNAL_CONTACT = 'external_contact';
    const TYPE_INAPPROPRIATE_CONTENT = 'inappropriate_content';
    const TYPE_PAYMENT_OUTSIDE = 'payment_outside';

    // Constantes para severidade
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

    // Métodos estáticos para criar alertas
    public static function createPhoneRequestAlert($conversationId, $triggeredBy, $content)
    {
        return self::create([
            'conversation_id' => $conversationId,
            'triggered_by' => $triggeredBy,
            'alert_type' => self::TYPE_PHONE_REQUEST,
            'triggered_content' => $content,
            'description' => 'Solicitação de número de telefone detectada',
            'severity' => self::SEVERITY_MEDIUM,
            'metadata' => [
                'warning_message' => '⚠️ ATENÇÃO: A plataforma DesejoLivre não rastreia conversas fora do chat. Para sua segurança, mantenha todas as comunicações aqui.',
                'recommendation' => 'Recomendamos manter a comunicação dentro da plataforma para maior segurança.'
            ]
        ]);
    }

    public static function createPersonalInfoAlert($conversationId, $triggeredBy, $content)
    {
        return self::create([
            'conversation_id' => $conversationId,
            'triggered_by' => $triggeredBy,
            'alert_type' => self::TYPE_PERSONAL_INFO,
            'triggered_content' => $content,
            'description' => 'Solicitação de informações pessoais detectada',
            'severity' => self::SEVERITY_HIGH,
            'metadata' => [
                'warning_message' => '🚨 ALERTA DE SEGURANÇA: Não compartilhe informações pessoais sensíveis. A plataforma não pode garantir a segurança de dados compartilhados fora do sistema.',
                'recommendation' => 'Mantenha conversas profissionais e evite compartilhar dados pessoais.'
            ]
        ]);
    }

    public static function createExternalContactAlert($conversationId, $triggeredBy, $content)
    {
        return self::create([
            'conversation_id' => $conversationId,
            'triggered_by' => $triggeredBy,
            'alert_type' => self::TYPE_EXTERNAL_CONTACT,
            'triggered_content' => $content,
            'description' => 'Tentativa de contato externo detectada',
            'severity' => self::SEVERITY_CRITICAL,
            'metadata' => [
                'warning_message' => '🚨 ALERTA CRÍTICO: Tentativa de contato fora da plataforma detectada. A DesejoLivre não pode garantir sua segurança em comunicações externas.',
                'recommendation' => 'Mantenha toda comunicação dentro da plataforma. Caso necessário, use o sistema de contratação oficial.'
            ]
        ]);
    }
}
