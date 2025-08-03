<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\CompanionProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

class MediaController extends Controller
{
    /**
     * Listar mídia de uma acompanhante
     */
    public function index(Request $request, CompanionProfile $companionProfile): JsonResponse
    {
        $this->authorize('view', $companionProfile);

        $query = $companionProfile->media()->ordered();

        // Filtrar por tipo
        if ($request->has('type')) {
            $type = $request->get('type');
            if (in_array($type, ['photo', 'video'])) {
                $query->where('file_type', $type);
            }
        }

        // Filtrar por status
        if ($request->has('status')) {
            $status = $request->get('status');
            switch ($status) {
                case 'approved':
                    $query->approved();
                    break;
                case 'pending':
                    $query->where('is_approved', false);
                    break;
                case 'verified':
                    $query->verified();
                    break;
            }
        }

        // Se não for o dono do perfil, mostrar apenas mídia pública
        if (!auth()->user() || auth()->user()->id !== $companionProfile->user_id) {
            $query->public();
        }

        $media = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $media->items(),
            'meta' => [
                'current_page' => $media->currentPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
                'last_page' => $media->lastPage(),
            ],
            'message' => 'Mídia listada com sucesso'
        ]);
    }

    /**
     * Mostrar mídia específica
     */
    public function show(Media $media): JsonResponse
    {
        $this->authorize('view', $media->companionProfile);

        // Verificar se a mídia é pública para usuários não-autorizados
        if (!auth()->user() || auth()->user()->id !== $media->companionProfile->user_id) {
            if (!$media->isPublic()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mídia não disponível'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $media,
            'message' => 'Detalhes da mídia'
        ]);
    }

    /**
     * Upload de mídia
     */
    public function store(Request $request, CompanionProfile $companionProfile): JsonResponse
    {
        $this->authorize('update', $companionProfile);

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:102400', // 100MB max
            'file_type' => 'required|in:photo,video',
            'description' => 'nullable|string|max:500',
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $fileType = $request->get('file_type');

        // Validações específicas por tipo
        if ($fileType === 'photo') {
            $photoValidator = Validator::make($request->all(), Media::getPhotoValidationRules());
            if ($photoValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo de imagem inválido',
                    'errors' => $photoValidator->errors()
                ], 422);
            }
        } else {
            $videoValidator = Validator::make($request->all(), Media::getVideoValidationRules());
            if ($videoValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo de vídeo inválido',
                    'errors' => $videoValidator->errors()
                ], 422);
            }
        }

        try {
            $media = $this->processFileUpload($file, $companionProfile, $fileType, $request);

            return response()->json([
                'success' => true,
                'data' => $media,
                'message' => 'Mídia enviada com sucesso'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar mídia
     */
    public function update(Request $request, Media $media): JsonResponse
    {
        $this->authorize('update', $media->companionProfile);

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:500',
            'is_primary' => 'boolean',
            'order' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Atualizar descrição
        if ($request->has('description')) {
            $media->update(['description' => $request->get('description')]);
        }

        // Definir como primária
        if ($request->get('is_primary', false)) {
            $media->setAsPrimary();
        }

        // Reordenar
        if ($request->has('order')) {
            $media->moveToPosition($request->get('order'));
        }

        return response()->json([
            'success' => true,
            'data' => $media->fresh(),
            'message' => 'Mídia atualizada com sucesso'
        ]);
    }

    /**
     * Excluir mídia
     */
    public function destroy(Media $media): JsonResponse
    {
        $this->authorize('update', $media->companionProfile);

        // Não permitir excluir a foto primária se for a única
        if ($media->is_primary && $media->companionProfile->media()->count() === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir a única foto do perfil'
            ], 422);
        }

        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mídia excluída com sucesso'
        ]);
    }

    /**
     * Definir como primária
     */
    public function setPrimary(Media $media): JsonResponse
    {
        $this->authorize('update', $media->companionProfile);

        $media->setAsPrimary();

        return response()->json([
            'success' => true,
            'data' => $media->fresh(),
            'message' => 'Mídia definida como primária'
        ]);
    }

    /**
     * Reordenar mídia
     */
    public function reorder(Request $request, CompanionProfile $companionProfile): JsonResponse
    {
        $this->authorize('update', $companionProfile);

        $validator = Validator::make($request->all(), [
            'media_ids' => 'required|array',
            'media_ids.*' => 'integer|exists:media,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $mediaIds = $request->get('media_ids');

        foreach ($mediaIds as $index => $mediaId) {
            $media = $companionProfile->media()->find($mediaId);
            if ($media) {
                $media->update(['order' => $index + 1]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Ordem da mídia atualizada'
        ]);
    }

    /**
     * Gerar thumbnail
     */
    public function generateThumbnail(Media $media): JsonResponse
    {
        $this->authorize('update', $media->companionProfile);

        if (!$media->isPhoto()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas fotos podem ter thumbnails'
            ], 422);
        }

        try {
            $this->processImageThumbnail($media);

            return response()->json([
                'success' => true,
                'message' => 'Thumbnail gerado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar thumbnail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processar upload de arquivo
     */
    private function processFileUpload($file, $companionProfile, $fileType, $request): Media
    {
        $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $filePath = 'companions/' . $companionProfile->id . '/' . $fileType . 's/' . $fileName;

        // Salvar arquivo
        Storage::disk('public')->put($filePath, file_get_contents($file));

        // Obter informações do arquivo
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        $mediaData = [
            'companion_profile_id' => $companionProfile->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'description' => $request->get('description'),
            'is_approved' => true, // Por padrão aprovado, pode ser alterado por admin
            'is_verified' => false, // Precisa ser verificado por admin
        ];

        // Processar imagem
        if ($fileType === 'photo') {
            $mediaData = array_merge($mediaData, $this->processImage($file, $filePath));
        }

        // Processar vídeo
        if ($fileType === 'video') {
            $mediaData = array_merge($mediaData, $this->processVideo($file, $filePath));
        }

        $media = Media::create($mediaData);

        // Definir como primária se solicitado
        if ($request->get('is_primary', false)) {
            $media->setAsPrimary();
        }

        return $media;
    }

    /**
     * Processar imagem
     */
    private function processImage($file, $filePath): array
    {
        $image = Image::make($file);
        $width = $image->width();
        $height = $image->height();

        // Criar thumbnail
        $thumbnail = $image->resize(300, 300, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $pathInfo = pathinfo($filePath);
        $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['basename'];

        Storage::disk('public')->put($thumbnailPath, $thumbnail->encode());

        return [
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * Processar vídeo
     */
    private function processVideo($file, $filePath): array
    {
        $data = [
            'width' => null,
            'height' => null,
            'duration' => null,
        ];

        try {
            // Usar FFmpeg para obter informações do vídeo
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => '/usr/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/bin/ffprobe',
            ]);

            $video = $ffmpeg->open(Storage::disk('public')->path($filePath));
            $stream = $video->getStreams()->first();

            if ($stream) {
                $data['width'] = $stream->get('width');
                $data['height'] = $stream->get('height');
                $data['duration'] = (int) $stream->get('duration');

                // Gerar thumbnail do vídeo
                $frame = $video->frame(TimeCode::fromSeconds(1));
                $pathInfo = pathinfo($filePath);
                $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '.jpg';

                $frame->save(Storage::disk('public')->path($thumbnailPath));
            }
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::warning('Erro ao processar vídeo: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Processar thumbnail de imagem existente
     */
    private function processImageThumbnail(Media $media): void
    {
        if (!Storage::disk('public')->exists($media->file_path)) {
            throw new \Exception('Arquivo original não encontrado');
        }

        $image = Image::make(Storage::disk('public')->path($media->file_path));

        // Criar thumbnail
        $thumbnail = $image->resize(300, 300, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $pathInfo = pathinfo($media->file_path);
        $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['basename'];

        Storage::disk('public')->put($thumbnailPath, $thumbnail->encode());
    }
}
