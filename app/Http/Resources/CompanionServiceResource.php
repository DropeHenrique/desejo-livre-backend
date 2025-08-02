<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanionServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'formatted_price' => $this->formatted_price,
            'has_price' => $this->hasPrice(),

            'service_type' => $this->whenLoaded('serviceType', function () {
                return new ServiceTypeResource($this->serviceType);
            }),
        ];
    }
}
