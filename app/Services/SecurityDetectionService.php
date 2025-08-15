<?php

namespace App\Services;

use App\Models\SecurityAlert;

class SecurityDetectionService
{
    // Padrões para detectar solicitações de telefone
    private array $phonePatterns = [
        '/\b(?:telefone|celular|whatsapp|zap|zapzap|wpp|fone|contato)\b/i',
        '/\b(?:número|num|tel|cel)\s+(?:de\s+)?(?:telefone|celular|whatsapp)\b/i',
        '/\b(?:me\s+)?(?:passe|passa|manda|envia|envie)\s+(?:seu|o\s+)?(?:telefone|celular|whatsapp|zap)\b/i',
        '/\b(?:qual\s+é\s+)?(?:seu|o\s+)?(?:telefone|celular|whatsapp|zap)\b/i',
        '/\b(?:chama|liga|telefona)\s+(?:para|no)\s+(?:mim|eu)\b/i',
    ];

    // Padrões para detectar solicitações de informações pessoais
    private array $personalInfoPatterns = [
        '/\b(?:nome\s+completo|nome\s+real|nome\s+verdadeiro)\b/i',
        '/\b(?:endereço|onde\s+mora|onde\s+você\s+mora)\b/i',
        '/\b(?:cpf|rg|documento|identidade)\b/i',
        '/\b(?:idade|quantos\s+anos|data\s+de\s+nascimento)\b/i',
        '/\b(?:instagram|facebook|twitter|redes\s+sociais)\b/i',
        '/\b(?:email|e-mail|correio\s+eletrônico)\b/i',
    ];

    // Padrões para detectar tentativas de contato externo
    private array $externalContactPatterns = [
        '/\b(?:instagram|facebook|twitter|tiktok|snapchat)\b/i',
        '/\b(?:telegram|signal|discord|skype)\b/i',
        '/\b(?:encontros|encontro|saída|saida|sair)\b/i',
        '/\b(?:hotel|motel|apartamento|casa)\b/i',
        '/\b(?:pix|transferência|transferencia|pagamento\s+externo)\b/i',
        '/\b(?:uber|99|cabify|taxi)\b/i',
    ];

    // Padrões para detectar conteúdo inadequado
    private array $inappropriatePatterns = [
        '/\b(?:prostituição|prostituicao|prostituir)\b/i',
        '/\b(?:tráfico|trafico|drogas|maconha|cocaína)\b/i',
        '/\b(?:menor\s+de\s+idade|adolescente|menor)\b/i',
        '/\b(?:violência|violencia|agressão|agressao)\b/i',
    ];

    /**
     * Analisa uma mensagem e retorna alertas de segurança se necessário
     */
    public function analyzeMessage(string $content, int $conversationId, int $senderId): array
    {
        $alerts = [];
        $content = strtolower(trim($content));

        // Verificar solicitações de telefone
        if ($this->detectPhoneRequest($content)) {
            $alert = SecurityAlert::createPhoneRequestAlert($conversationId, $senderId, $content);
            $alerts[] = $alert;
        }

        // Verificar solicitações de informações pessoais
        if ($this->detectPersonalInfoRequest($content)) {
            $alert = SecurityAlert::createPersonalInfoAlert($conversationId, $senderId, $content);
            $alerts[] = $alert;
        }

        // Verificar tentativas de contato externo
        if ($this->detectExternalContact($content)) {
            $alert = SecurityAlert::createExternalContactAlert($conversationId, $senderId, $content);
            $alerts[] = $alert;
        }

        // Verificar conteúdo inadequado
        if ($this->detectInappropriateContent($content)) {
            $alert = SecurityAlert::create([
                'conversation_id' => $conversationId,
                'triggered_by' => $senderId,
                'alert_type' => SecurityAlert::TYPE_INAPPROPRIATE_CONTENT,
                'triggered_content' => $content,
                'description' => 'Conteúdo inadequado detectado',
                'severity' => SecurityAlert::SEVERITY_HIGH,
                'metadata' => [
                    'warning_message' => '🚨 ALERTA: Conteúdo inadequado detectado. A plataforma não tolera atividades ilegais ou inadequadas.',
                    'recommendation' => 'Mantenha conversas respeitosas e dentro da legalidade.'
                ]
            ]);
            $alerts[] = $alert;
        }

        return $alerts;
    }

    /**
     * Detecta solicitações de telefone
     */
    private function detectPhoneRequest(string $content): bool
    {
        foreach ($this->phonePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Detecta solicitações de informações pessoais
     */
    private function detectPersonalInfoRequest(string $content): bool
    {
        foreach ($this->personalInfoPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Detecta tentativas de contato externo
     */
    private function detectExternalContact(string $content): bool
    {
        foreach ($this->externalContactPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Detecta conteúdo inadequado
     */
    private function detectInappropriateContent(string $content): bool
    {
        foreach ($this->inappropriatePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica se uma mensagem contém números de telefone
     */
    public function containsPhoneNumber(string $content): bool
    {
        // Padrões para números de telefone brasileiros
        $phonePatterns = [
            '/\b\d{2}\s*\d{4,5}\s*-?\s*\d{4}\b/', // (11) 99999-9999
            '/\b\d{2}\s*\d{4,5}\s*\d{4}\b/', // 11 99999 9999
            '/\b\d{10,11}\b/', // 11999999999
        ];

        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica se uma mensagem contém emails
     */
    public function containsEmail(string $content): bool
    {
        return preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $content);
    }

    /**
     * Verifica se uma mensagem contém CPF
     */
    public function containsCPF(string $content): bool
    {
        return preg_match('/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b/', $content);
    }
}
