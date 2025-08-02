<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'uf' => $this->uf,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'cities' => $this->whenLoaded('cities', function () {
                return CityResource::collection($this->cities);
            }),
        ];
    }
}
