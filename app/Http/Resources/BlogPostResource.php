<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'featured_image' => $this->featured_image,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'reading_time' => $this->reading_time,
            'featured_image_url' => $this->featured_image_url,

            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            'categories' => $this->whenLoaded('categories', function () {
                return BlogCategoryResource::collection($this->categories);
            }),
        ];
    }
}
