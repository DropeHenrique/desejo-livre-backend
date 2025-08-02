<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\State;
use App\Models\City;
use App\Models\District;
use App\Models\Plan;
use App\Models\CompanionProfile;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeders que devem ser executados sempre
        $this->call([
            StateSeeder::class,
            PlanSeeder::class,
        ]);

        // Create states
        $states = [
            ['name' => 'São Paulo', 'uf' => 'SP'],
            ['name' => 'Rio de Janeiro', 'uf' => 'RJ'],
            ['name' => 'Minas Gerais', 'uf' => 'MG'],
            ['name' => 'Bahia', 'uf' => 'BA'],
            ['name' => 'Rio Grande do Sul', 'uf' => 'RS'],
        ];

        foreach ($states as $stateData) {
            $state = State::create($stateData);

            // Create cities for each state
            $cities = [
                ['name' => $stateData['name'] . ' Capital', 'state_id' => $state->id],
                ['name' => 'Santos', 'state_id' => $state->id],
                ['name' => 'Campinas', 'state_id' => $state->id],
            ];

            foreach ($cities as $cityData) {
                $city = City::create($cityData);

                // Create districts for each city
                $districts = [
                    ['name' => 'Centro', 'city_id' => $city->id],
                    ['name' => 'Vila Madalena', 'city_id' => $city->id],
                    ['name' => 'Copacabana', 'city_id' => $city->id],
                    ['name' => 'Ipanema', 'city_id' => $city->id],
                ];

                foreach ($districts as $districtData) {
                    District::create($districtData);
                }
            }
        }

        // Create plans
        $companionPlans = [
            [
                'name' => 'Bronze',
                'description' => 'Plano básico para acompanhantes',
                'price' => 29.90,
                'duration_days' => 30,
                'user_type' => 'companion',
                'features' => ['Perfil básico', 'Upload de fotos', 'Até 5 fotos'],
                'active' => true,
            ],
            [
                'name' => 'Prata',
                'description' => 'Plano intermediário para acompanhantes',
                'price' => 49.90,
                'duration_days' => 30,
                'user_type' => 'companion',
                'features' => ['Perfil básico', 'Upload de fotos', 'Até 10 fotos', 'Destaque na busca'],
                'active' => true,
            ],
            [
                'name' => 'Ouro',
                'description' => 'Plano premium para acompanhantes',
                'price' => 79.90,
                'duration_days' => 30,
                'user_type' => 'companion',
                'features' => ['Perfil básico', 'Upload de fotos', 'Até 20 fotos', 'Upload de vídeos', 'Destaque premium'],
                'active' => true,
            ],
        ];

        foreach ($companionPlans as $planData) {
            Plan::create($planData);
        }

        $clientPlans = [
            [
                'name' => 'Básico',
                'description' => 'Plano básico para clientes',
                'price' => 9.90,
                'duration_days' => 30,
                'user_type' => 'client',
                'features' => ['Busca básica', 'Visualizar perfis'],
                'active' => true,
            ],
            [
                'name' => 'Premium',
                'description' => 'Plano premium para clientes',
                'price' => 19.90,
                'duration_days' => 30,
                'user_type' => 'client',
                'features' => ['Busca básica', 'Visualizar perfis', 'Filtros avançados', 'Favoritos ilimitados'],
                'active' => true,
            ],
        ];

        foreach ($clientPlans as $planData) {
            Plan::create($planData);
        }

        // Create admin user
        $admin = User::create([
            'name' => 'Admin DesejoLivre',
            'email' => 'admin@desejolivre.com',
            'password' => bcrypt('admin123'),
            'user_type' => 'admin',
            'phone' => '(11) 99999-9999',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        // Create some test clients
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Cliente {$i}",
                'email' => "cliente{$i}@teste.com",
                'password' => bcrypt('password123'),
                'user_type' => 'client',
                'phone' => "(11) 9999{$i}-000{$i}",
                'active' => true,
                'email_verified_at' => now(),
            ]);
        }

        // Create some test companions with profiles
        $cities = City::all();
        $plans = Plan::where('user_type', 'companion')->get();

        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => "Acompanhante {$i}",
                'email' => "acompanhante{$i}@teste.com",
                'password' => bcrypt('password123'),
                'user_type' => 'companion',
                'phone' => "(11) 8888{$i}-000{$i}",
                'active' => true,
                'email_verified_at' => now(),
            ]);

            $profile = CompanionProfile::create([
                'user_id' => $user->id,
                'artistic_name' => "Bella {$i}",
                'age' => rand(21, 35),
                'hide_age' => false,
                'about_me' => "Olá! Sou a Bella {$i}, uma acompanhante carinhosa e educada. Estou aqui para proporcionar momentos únicos e especiais.",
                'height' => rand(160, 175),
                'weight' => rand(50, 70),
                'eye_color' => ['castanhos', 'verdes', 'azuis'][array_rand(['castanhos', 'verdes', 'azuis'])],
                'hair_color' => ['loiro', 'moreno', 'ruivo'][array_rand(['loiro', 'moreno', 'ruivo'])],
                'ethnicity' => ['branca', 'morena', 'negra'][array_rand(['branca', 'morena', 'negra'])],
                'has_tattoos' => rand(0, 1),
                'has_piercings' => rand(0, 1),
                'has_silicone' => rand(0, 1),
                'is_smoker' => rand(0, 1),
                'verified' => $i <= 7, // 70% verificadas
                'verification_date' => $i <= 7 ? now()->subDays(rand(1, 30)) : null,
                'online_status' => rand(0, 1),
                'last_active' => now()->subMinutes(rand(1, 1440)),
                'plan_id' => $plans->random()->id,
                'plan_expires_at' => now()->addDays(rand(1, 60)),
                'city_id' => $cities->random()->id,
                'whatsapp' => "(11) 7777{$i}-000{$i}",
                'telegram' => "@bella{$i}",
            ]);
        }

        $this->command->info('Database seeded successfully!');
    }
}
