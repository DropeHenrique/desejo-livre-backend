<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'user_type' => $this->user_type,
            'phone' => $this->phone,
            'active' => $this->active,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Dados de endereço
            'cep' => $this->cep,
            'address' => $this->address,
            'complement' => $this->complement,
            'formatted_address' => $this->getFormattedAddress(),
            'short_address' => $this->getShortAddress(),
            'has_complete_address' => $this->hasCompleteAddress(),

            // Relacionamentos
            'companion_profile' => $this->whenLoaded('companionProfile', function () {
                return new CompanionProfileResource($this->companionProfile);
            }),
            'subscriptions' => $this->whenLoaded('subscriptions', function () {
                return SubscriptionResource::collection($this->subscriptions);
            }),
            'payments' => $this->whenLoaded('payments', function () {
                return PaymentResource::collection($this->payments);
            }),

            // Relacionamentos de endereço
            'state' => $this->whenLoaded('state', function () {
                return new StateResource($this->state);
            }),
            'city' => $this->whenLoaded('city', function () {
                return new CityResource($this->city);
            }),
            'district' => $this->whenLoaded('district', function () {
                return new DistrictResource($this->district);
            }),
        ];
    }
}
