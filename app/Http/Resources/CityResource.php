<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'state' => $this->whenLoaded('state', function () {
                return new StateResource($this->state);
            }),
            'districts' => $this->whenLoaded('districts', function () {
                return DistrictResource::collection($this->districts);
            }),
            'companion_profiles_count' => $this->when(isset($this->companion_profiles_count), $this->companion_profiles_count),
        ];
    }
}
