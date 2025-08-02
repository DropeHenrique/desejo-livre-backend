<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'is_primary' => $this->is_primary,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'formatted_size' => $this->formatted_size,

            'companion_profile' => $this->whenLoaded('companionProfile', function () {
                return new CompanionProfileResource($this->companionProfile);
            }),
        ];
    }
}
