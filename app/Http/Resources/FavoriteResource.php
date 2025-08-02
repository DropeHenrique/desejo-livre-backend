<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'companion_profile' => $this->whenLoaded('companionProfile', function () {
                return new CompanionProfileResource($this->companionProfile);
            }),
        ];
    }
}
