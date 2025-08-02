<?php

namespace Database\Factories;

use App\Models\CompanionProfile;
use App\Models\User;
use App\Models\City;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanionProfileFactory extends Factory
{
    protected $model = CompanionProfile::class;

    private static $artisticNames = [
        'Luna', 'Sofia', 'Isabella', 'Valentina', 'Camila', 'Gabriela',
        'Nicole', 'Mariana', 'Beatriz', 'Amanda', 'Rafaela', 'Letícia',
        'Fernanda', 'Juliana', 'Carolina', 'Larissa', 'Vanessa', 'Patrícia',
        'Natália', 'Débora', 'Priscila', 'Mônica', 'Adriana', 'Cristina',
        'Sabrina', 'Tatiana', 'Renata', 'Elaine', 'Simone', 'Luciana'
    ];

    private static $eyeColors = [
        'castanhos', 'verdes', 'azuis', 'pretos', 'mel', 'amendoados'
    ];

    private static $hairColors = [
        'loiro', 'moreno', 'ruivo', 'preto', 'castanho', 'platinado'
    ];

    private static $ethnicities = [
        'branca', 'morena', 'negra', 'asiática', 'indígena', 'mulata'
    ];

    public function definition(): array
    {
        $age = $this->faker->numberBetween(18, 40);
        $hideAge = $this->faker->boolean(30); // 30% chance de esconder idade

        return [
            'user_id' => User::factory()->create(['user_type' => 'companion']),
            'artistic_name' => $this->faker->randomElement(self::$artisticNames),
            'slug' => $this->faker->unique()->slug,
            'age' => $hideAge ? null : $age,
            'hide_age' => $hideAge,
            'about_me' => $this->faker->paragraph(3),
            'height' => $this->faker->numberBetween(150, 180), // cm
            'weight' => $this->faker->numberBetween(45, 80), // kg
            'eye_color' => $this->faker->randomElement(self::$eyeColors),
            'hair_color' => $this->faker->randomElement(self::$hairColors),
            'ethnicity' => $this->faker->randomElement(self::$ethnicities),
            'has_tattoos' => $this->faker->boolean(40),
            'has_piercings' => $this->faker->boolean(30),
            'has_silicone' => $this->faker->boolean(25),
            'is_smoker' => $this->faker->boolean(20),
            'verified' => $this->faker->boolean(70), // 70% verificado
            'verification_date' => $this->faker->boolean(70) ? $this->faker->dateTimeThisYear : null,
            'online_status' => $this->faker->boolean(40), // 40% online
            'last_active' => $this->faker->dateTimeThisMonth,
            'plan_id' => Plan::factory()->companion(),
            'plan_expires_at' => $this->faker->dateTimeBetween('now', '+3 months'),
            'city_id' => City::factory(),
            'whatsapp' => $this->faker->phoneNumber,
            'telegram' => '@' . $this->faker->userName,
        ];
    }

    public function verified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'verified' => true,
                'verification_date' => $this->faker->dateTimeThisYear,
            ];
        });
    }

    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'verified' => false,
                'verification_date' => null,
            ];
        });
    }

    public function online(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'online_status' => true,
                'last_active' => now(),
            ];
        });
    }

    public function offline(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'online_status' => false,
                'last_active' => $this->faker->dateTimeThisWeek,
            ];
        });
    }

    public function withActivePlan(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'plan_id' => Plan::factory()->companion(),
                'plan_expires_at' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
            ];
        });
    }

    public function withExpiredPlan(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'plan_id' => Plan::factory()->companion(),
                'plan_expires_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            ];
        });
    }
}
