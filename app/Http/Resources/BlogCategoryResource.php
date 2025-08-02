<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'posts_count' => $this->posts_count,
            'latest_post' => $this->whenLoaded('latestPost', function () {
                return new BlogPostResource($this->latestPost);
            }),

            'posts' => $this->whenLoaded('posts', function () {
                return BlogPostResource::collection($this->posts);
            }),
        ];
    }
}
