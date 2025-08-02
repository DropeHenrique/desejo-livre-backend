<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $expiresAt = $this->faker->dateTimeBetween($startsAt, '+1 year');

        return [
            'user_id' => User::factory()->create(['user_type' => 'client']),
            'plan_id' => Plan::factory()->client(),
            'status' => $this->faker->randomElement(['active', 'canceled', 'expired']),
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ];
    }

    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'starts_at' => now()->subDays(rand(1, 30)),
                'expires_at' => now()->addDays(rand(1, 365)),
            ];
        });
    }

    public function canceled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'canceled',
                'starts_at' => now()->subDays(rand(1, 30)),
                'expires_at' => now()->addDays(rand(1, 365)),
            ];
        });
    }

    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'expired',
                'starts_at' => now()->subDays(rand(30, 90)),
                'expires_at' => now()->subDays(rand(1, 29)),
            ];
        });
    }

    public function forCompanion(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::factory()->create(['user_type' => 'companion']),
                'plan_id' => Plan::factory()->companion(),
            ];
        });
    }

    public function forClient(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::factory()->create(['user_type' => 'client']),
                'plan_id' => Plan::factory()->client(),
            ];
        });
    }
}
