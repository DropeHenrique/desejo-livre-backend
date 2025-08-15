<?php

namespace App\Services;

use App\Models\FacialVerification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class FacialRecognitionService
{
    private const FACE_SIMILARITY_THRESHOLD = 0.6; // Threshold para similaridade facial
    private const UPLOAD_PATH = 'facial-verification';

    /**
     * Processa upload de documentos e fotos para verificação facial
     */
    public function processVerification(
        User $user,
        UploadedFile $documentFront,
        UploadedFile $documentBack,
        UploadedFile $facePhoto,
        UploadedFile $documentWithFace
    ): FacialVerification {
        try {
            // Salvar arquivos
            $documentFrontPath = $this->saveFile($documentFront, 'document_front');
            $documentBackPath = $this->saveFile($documentBack, 'document_back');
            $facePhotoPath = $this->saveFile($facePhoto, 'face_photo');
            $documentWithFacePath = $this->saveFile($documentWithFace, 'document_with_face');

            // Extrair encoding facial da foto do rosto
            $faceEncoding = $this->extractFaceEncoding($facePhotoPath);

            // Criar ou atualizar verificação facial
            $verification = FacialVerification::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'face_encoding' => $faceEncoding,
                    'document_front_path' => $documentFrontPath,
                    'document_back_path' => $documentBackPath,
                    'face_photo_path' => $facePhotoPath,
                    'document_with_face_path' => $documentWithFacePath,
                    'status' => 'pending',
                ]
            );

            Log::info('Verificação facial processada', [
                'user_id' => $user->id,
                'verification_id' => $verification->id
            ]);

            return $verification;

        } catch (Exception $e) {
            Log::error('Erro ao processar verificação facial', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Verifica se uma foto corresponde ao usuário
     */
    public function verifyFace(User $user, UploadedFile $faceImage): bool
    {
        try {
            $verification = $user->facialVerification;

            if (!$verification || !$verification->isApproved()) {
                return false;
            }

            // Salvar imagem temporária para processamento
            $tempPath = $this->saveFile($faceImage, 'temp_verification');

            // Extrair encoding da imagem de verificação
            $verificationEncoding = $this->extractFaceEncoding($tempPath);

            if (!$verificationEncoding) {
                Storage::delete($tempPath);
                return false;
            }

            // Comparar encodings
            $similarity = $this->compareFaceEncodings(
                $verification->face_encoding,
                $verificationEncoding
            );

            // Limpar arquivo temporário
            Storage::delete($tempPath);

            $isMatch = $similarity >= self::FACE_SIMILARITY_THRESHOLD;

            if ($isMatch) {
                $verification->recordFaceLogin();
            }

            Log::info('Verificação facial realizada', [
                'user_id' => $user->id,
                'similarity' => $similarity,
                'is_match' => $isMatch
            ]);

            return $isMatch;

        } catch (Exception $e) {
            Log::error('Erro na verificação facial', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Aprova verificação facial
     */
    public function approveVerification(FacialVerification $verification): void
    {
        $verification->approve();

        Log::info('Verificação facial aprovada', [
            'verification_id' => $verification->id,
            'user_id' => $verification->user_id
        ]);
    }

    /**
     * Rejeita verificação facial
     */
    public function rejectVerification(FacialVerification $verification, string $reason): void
    {
        $verification->reject($reason);

        Log::info('Verificação facial rejeitada', [
            'verification_id' => $verification->id,
            'user_id' => $verification->user_id,
            'reason' => $reason
        ]);
    }

    /**
     * Salva arquivo no storage
     */
    private function saveFile(UploadedFile $file, string $prefix): string
    {
        $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(self::UPLOAD_PATH, $filename, 'private');

        return $path;
    }

    /**
     * Extrai encoding facial de uma imagem
     */
    private function extractFaceEncoding(string $imagePath): ?string
    {
        try {
            // Usar Python script para extrair encoding facial
            $fullPath = Storage::path($imagePath);

            $command = "python3 " . base_path('scripts/extract_face_encoding.py') . " " . escapeshellarg($fullPath);
            $output = shell_exec($command);

            if ($output && trim($output) !== '') {
                return trim($output);
            }

            return null;

        } catch (Exception $e) {
            Log::error('Erro ao extrair encoding facial', [
                'image_path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Compara dois encodings faciais
     */
    private function compareFaceEncodings(string $encoding1, string $encoding2): float
    {
        try {
            // Usar Python script para comparar encodings
            $command = "python3 " . base_path('scripts/compare_face_encodings.py') . " " .
                      escapeshellarg($encoding1) . " " . escapeshellarg($encoding2);

            $output = shell_exec($command);

            if ($output && is_numeric(trim($output))) {
                return (float) trim($output);
            }

            return 0.0;

        } catch (Exception $e) {
            Log::error('Erro ao comparar encodings faciais', [
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Valida se uma imagem contém um rosto
     */
    public function validateFaceImage(UploadedFile $image): bool
    {
        try {
            $tempPath = $this->saveFile($image, 'temp_validation');
            $encoding = $this->extractFaceEncoding($tempPath);

            Storage::delete($tempPath);

            return $encoding !== null;

        } catch (Exception $e) {
            Log::error('Erro ao validar imagem facial', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtém estatísticas de verificação facial
     */
    public function getVerificationStats(): array
    {
        return [
            'total' => FacialVerification::count(),
            'pending' => FacialVerification::pending()->count(),
            'approved' => FacialVerification::approved()->count(),
            'rejected' => FacialVerification::rejected()->count(),
        ];
    }
}
