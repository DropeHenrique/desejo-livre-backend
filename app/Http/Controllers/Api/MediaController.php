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

class MediaController extends Controller
{
    /**
     * Listar mídia de uma acompanhante
     */
    public function index(Request $request, CompanionProfile $companionProfile): JsonResponse
    {
        $this->authorize('view', $companionProfile);

        $query = $companionProfile->media();

        if ($request->type) {
            $query->where('file_type', $request->type);
        }

        if ($request->primary) {
            $query->where('is_primary', $request->primary);
        }

        $media = $query->orderBy('is_primary', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $media->items(),
            'meta' => [
                'current_page' => $media->currentPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
                'last_page' => $media->lastPage(),
            ]
        ]);
    }

    /**
     * Mostrar mídia específica
     */
    public function show(Media $media): JsonResponse
    {
        $this->authorize('view', $media->companionProfile);

        return response()->json([
            'data' => $media
        ]);
    }

    /**
     * Upload de mídia
     */
    public function store(Request $request, CompanionProfile $companionProfile): JsonResponse
    {
        $this->authorize('update', $companionProfile);

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpeg,png,jpg,webp,mp4,webm|max:10240', // 10MB max
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $fileType = $this->getFileType($file->getMimeType());
        $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $filePath = 'companions/' . $companionProfile->id . '/' . $fileType . 's/' . $fileName;

        // Upload do arquivo
        $file->storeAs('public/' . dirname($filePath), $fileName);

        // Criar registro no banco
        $media = Media::create([
            'companion_profile_id' => $companionProfile->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $file->getSize(),
            'is_primary' => $request->is_primary ?? false,
        ]);

        // Se for marcado como primário, remover primário dos outros
        if ($media->is_primary) {
            $media->setAsPrimary();
        }

        return response()->json([
            'message' => 'Mídia enviada com sucesso',
            'data' => $media
        ], 201);
    }

    /**
     * Atualizar mídia
     */
    public function update(Request $request, Media $media): JsonResponse
    {
        $this->authorize('update', $media->companionProfile);

        $validator = Validator::make($request->all(), [
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('is_primary') && $request->is_primary) {
            $media->setAsPrimary();
        }

        return response()->json([
            'message' => 'Mídia atualizada com sucesso',
            'data' => $media->fresh()
        ]);
    }

    /**
     * Excluir mídia
     */
    public function destroy(Media $media): JsonResponse
    {
        $this->authorize('update', $media->companionProfile);

        // Excluir arquivo do storage
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        // Excluir thumbnail se existir
        if ($media->isPhoto()) {
            $thumbnailPath = $this->getThumbnailPath($media->file_path);
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }
        }

        $media->delete();

        return response()->json([
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
            'message' => 'Mídia definida como primária',
            'data' => $media->fresh()
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
            'media_ids.*' => 'exists:media,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->media_ids as $index => $mediaId) {
            Media::where('id', $mediaId)
                 ->where('companion_profile_id', $companionProfile->id)
                 ->update(['order' => $index + 1]);
        }

        return response()->json([
            'message' => 'Mídia reordenada com sucesso'
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
                'message' => 'Apenas fotos podem ter thumbnails'
            ], 422);
        }

        // Aqui você implementaria a geração de thumbnail
        // Por exemplo, usando Intervention Image

        return response()->json([
            'message' => 'Thumbnail gerado com sucesso'
        ]);
    }

    /**
     * Determinar tipo de arquivo
     */
    private function getFileType(string $mimeType): string
    {
        if (Str::startsWith($mimeType, 'image/')) {
            return 'photo';
        }

        if (Str::startsWith($mimeType, 'video/')) {
            return 'video';
        }

        return 'photo'; // fallback
    }

    /**
     * Obter caminho do thumbnail
     */
    private function getThumbnailPath(string $filePath): string
    {
        $pathInfo = pathinfo($filePath);
        return $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['basename'];
    }
}
