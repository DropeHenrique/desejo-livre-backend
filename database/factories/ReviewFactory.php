<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use App\Models\CompanionProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['user_type' => 'client']),
            'companion_profile_id' => CompanionProfile::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->paragraph(2),
            'is_verified' => $this->faker->boolean(30), // 30% chance de ser verificado
            'is_anonymous' => $this->faker->boolean(20), // 20% chance de ser anônimo
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
        ];
    }

    public function verified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_verified' => true,
                'status' => 'approved',
            ];
        });
    }

    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_verified' => false,
                'status' => 'pending',
            ];
        });
    }

    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
            ];
        });
    }

    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    public function anonymous(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_anonymous' => true,
            ];
        });
    }

    public function withHighRating(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'rating' => $this->faker->numberBetween(4, 5),
            ];
        });
    }

    public function withLowRating(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'rating' => $this->faker->numberBetween(1, 2),
            ];
        });
    }
}
