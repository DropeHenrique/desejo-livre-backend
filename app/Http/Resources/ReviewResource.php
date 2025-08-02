<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_verified' => $this->is_verified,
            'is_anonymous' => $this->is_anonymous,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'stars' => $this->stars,
            'display_name' => $this->display_name,

            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            'companion_profile' => $this->whenLoaded('companionProfile', function () {
                return new CompanionProfileResource($this->companionProfile);
            }),
        ];
    }
}
