<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanionProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'artistic_name' => $this->artistic_name,
            'slug' => $this->slug,
            'age' => $this->display_age,
            'hide_age' => $this->hide_age,
            'about_me' => $this->about_me,
            'height' => $this->formatted_height,
            'weight' => $this->formatted_weight,
            'eye_color' => $this->eye_color,
            'hair_color' => $this->hair_color,
            'ethnicity' => $this->ethnicity,
            'has_tattoos' => $this->has_tattoos,
            'has_piercings' => $this->has_piercings,
            'has_silicone' => $this->has_silicone,
            'is_smoker' => $this->is_smoker,
            'verified' => $this->verified,
            'verification_date' => $this->verification_date,
            'online_status' => $this->online_status,
            'last_active' => $this->last_active,
            'plan_expires_at' => $this->plan_expires_at,
            'whatsapp' => $this->whatsapp,
            'telegram' => $this->telegram,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relacionamentos
            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            'city' => $this->whenLoaded('city', function () {
                return new CityResource($this->city);
            }),
            'plan' => $this->whenLoaded('plan', function () {
                return new PlanResource($this->plan);
            }),
            'services' => $this->whenLoaded('services', function () {
                return CompanionServiceResource::collection($this->services);
            }),
            'districts' => $this->whenLoaded('districts', function () {
                return DistrictResource::collection($this->districts);
            }),
            'media' => $this->whenLoaded('media', function () {
                return MediaResource::collection($this->media);
            }),
            'photos' => $this->whenLoaded('photos', function () {
                return MediaResource::collection($this->photos);
            }),
            'videos' => $this->whenLoaded('videos', function () {
                return MediaResource::collection($this->videos);
            }),
            'primary_photo' => $this->whenLoaded('primaryPhoto', function () {
                return new MediaResource($this->primaryPhoto);
            }),
            'reviews' => $this->whenLoaded('reviews', function () {
                return ReviewResource::collection($this->reviews);
            }),

            // Atributos calculados
            'average_rating' => $this->averageRating(),
            'total_reviews' => $this->totalReviews(),
            'has_active_plan' => $this->hasActivePlan(),
            'primary_photo_url' => $this->primary_photo_url,
            'thumbnail_url' => $this->thumbnail_url,
        ];
    }
}
