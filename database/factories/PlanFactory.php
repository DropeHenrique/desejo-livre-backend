<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    private static $companionPlans = [
        ['name' => 'Bronze', 'price' => 29.90, 'duration_days' => 30],
        ['name' => 'Prata', 'price' => 49.90, 'duration_days' => 30],
        ['name' => 'Ouro', 'price' => 79.90, 'duration_days' => 30],
        ['name' => 'Black', 'price' => 129.90, 'duration_days' => 30],
    ];

    private static $clientPlans = [
        ['name' => 'Básico', 'price' => 9.90, 'duration_days' => 30],
        ['name' => 'Premium', 'price' => 19.90, 'duration_days' => 30],
        ['name' => 'VIP', 'price' => 39.90, 'duration_days' => 30],
    ];

    public function definition(): array
    {
        static $companionCounter = 0;
        static $clientCounter = 0;

        $userType = $this->faker->randomElement(['companion', 'client']);

        if ($userType === 'companion') {
            $plans = self::$companionPlans;
            $plan = $plans[$companionCounter % count($plans)];
            $companionCounter++;
        } else {
            $plans = self::$clientPlans;
            $plan = $plans[$clientCounter % count($plans)];
            $clientCounter++;
        }

        return [
            'name' => $plan['name'],
            'slug' => Str::slug($plan['name']) . '-' . uniqid(),
            'description' => $this->faker->paragraph,
            'price' => $plan['price'],
            'duration_days' => $plan['duration_days'],
            'user_type' => $userType,
            'features' => $this->generateFeatures($userType, $plan['name']),
            'active' => true,
        ];
    }

    private function generateFeatures(string $userType, string $planName): array
    {
        if ($userType === 'companion') {
            $baseFeatures = ['Perfil básico', 'Upload de fotos'];

            if ($planName === 'Prata') {
                $baseFeatures[] = 'Destaque na busca';
                $baseFeatures[] = 'Até 10 fotos';
            } elseif ($planName === 'Ouro') {
                $baseFeatures[] = 'Destaque premium';
                $baseFeatures[] = 'Até 20 fotos';
                $baseFeatures[] = 'Upload de vídeos';
            } elseif ($planName === 'Black') {
                $baseFeatures[] = 'Máximo destaque';
                $baseFeatures[] = 'Fotos ilimitadas';
                $baseFeatures[] = 'Vídeos ilimitados';
                $baseFeatures[] = 'Suporte prioritário';
            }
        } else {
            $baseFeatures = ['Busca básica', 'Visualizar perfis'];

            if ($planName === 'Premium') {
                $baseFeatures[] = 'Filtros avançados';
                $baseFeatures[] = 'Favoritos ilimitados';
            } elseif ($planName === 'VIP') {
                $baseFeatures[] = 'Acesso prioritário';
                $baseFeatures[] = 'Chat direto';
                $baseFeatures[] = 'Sem anúncios';
            }
        }

        return $baseFeatures;
    }

    public function companion(): static
    {
        return $this->state(function (array $attributes) {
            static $counter = 0;
            $plans = self::$companionPlans;
            $plan = $plans[$counter % count($plans)];
            $counter++;

            return [
                'user_type' => 'companion',
                'name' => $plan['name'],
                'slug' => Str::slug($plan['name']) . '-' . uniqid(),
                'price' => $plan['price'],
                'duration_days' => $plan['duration_days'],
                'features' => $this->generateFeatures('companion', $plan['name']),
            ];
        });
    }

    public function client(): static
    {
        return $this->state(function (array $attributes) {
            static $counter = 0;
            $plans = self::$clientPlans;
            $plan = $plans[$counter % count($plans)];
            $counter++;

            return [
                'user_type' => 'client',
                'name' => $plan['name'],
                'slug' => Str::slug($plan['name']) . '-' . uniqid(),
                'price' => $plan['price'],
                'duration_days' => $plan['duration_days'],
                'features' => $this->generateFeatures('client', $plan['name']),
            ];
        });
    }

    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => true,
            ];
        });
    }

    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => false,
            ];
        });
    }
}
