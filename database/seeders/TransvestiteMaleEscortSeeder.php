<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CompanionProfile;
use App\Models\State;
use App\Models\City;
use App\Models\District;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;

class TransvestiteMaleEscortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Criando usuários travestis e garotos de programa...\n";

        // Buscar localizações de exemplo
        $sp = State::where('uf', 'SP')->first();
        $rj = State::where('uf', 'RJ')->first();
        $mg = State::where('uf', 'MG')->first();

        if ($sp) {
            $saoPaulo = City::where('name', 'São Paulo')->where('state_id', $sp->id)->first();
            if ($saoPaulo) {
                $this->createTransvestitesAndMaleEscorts($saoPaulo, $sp);
            }
        }

        if ($rj) {
            $rioJaneiro = City::where('name', 'Rio de Janeiro')->where('state_id', $rj->id)->first();
            if ($rioJaneiro) {
                $this->createTransvestitesAndMaleEscorts($rioJaneiro, $rj);
            }
        }

        if ($mg) {
            $beloHorizonte = City::where('name', 'Belo Horizonte')->where('state_id', $mg->id)->first();
            if ($beloHorizonte) {
                $this->createTransvestitesAndMaleEscorts($beloHorizonte, $mg);
            }
        }

        echo "\n🎯 Usuários travestis e garotos de programa criados com sucesso!\n";
        echo "\n📋 Credenciais de acesso:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "👗 TRAVESTIS:\n";
        echo "   Email: travesti1@teste.com | Senha: password\n";
        echo "   Email: travesti2@teste.com | Senha: password\n";
        echo "   Email: travesti3@teste.com | Senha: password\n";
        echo "\n💪 GAROTOS DE PROGRAMA:\n";
        echo "   Email: garoto1@teste.com | Senha: password\n";
        echo "   Email: garoto2@teste.com | Senha: password\n";
        echo "   Email: garoto3@teste.com | Senha: password\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }

    private function createTransvestitesAndMaleEscorts(City $city, State $state): void
    {
        // Buscar bairros da cidade
        $districts = District::where('city_id', $city->id)->take(3)->get();
        if ($districts->isEmpty()) {
            $districts = collect([
                District::create(['name' => 'Centro', 'city_id' => $city->id]),
                District::create(['name' => 'Zona Sul', 'city_id' => $city->id]),
                District::create(['name' => 'Zona Norte', 'city_id' => $city->id]),
            ]);
        }

        // Buscar plano básico
        $basicPlan = Plan::where('name', 'Básico')->first();

        // Criar travestis
        $this->createTransvestites($city, $state, $districts, $basicPlan);

        // Criar garotos de programa
        $this->createMaleEscorts($city, $state, $districts, $basicPlan);
    }

    private function createTransvestites(City $city, State $state, $districts, $basicPlan): void
    {
        $transvestites = [
            [
                'name' => 'Valentina Santos',
                'email' => 'travesti1@teste.com',
                'artistic_name' => 'Valentina',
                'age' => 25,
                'about_me' => 'Travesti profissional, ofereço serviços de qualidade com total discrição. Especialista em massagens relaxantes e companhia.',
                'height' => 175,
                'weight' => 65,
                'hair_color' => 'loiro',
                'eye_color' => 'azul',
                'ethnicity' => 'branca',
                'has_tattoos' => true,
                'has_piercings' => true,
                'has_silicone' => true,
                'is_smoker' => false,
                'attends_home' => true,
                'travel_radius_km' => 20,
                'whatsapp' => '(11) 91111-1111',
                'telegram' => '@valentina_santos',
            ],
            [
                'name' => 'Bianca Ferreira',
                'email' => 'travesti2@teste.com',
                'artistic_name' => 'Bianca',
                'age' => 28,
                'about_me' => 'Travesti elegante e sofisticada. Ofereço serviços de alta qualidade com foco na satisfação do cliente.',
                'height' => 170,
                'weight' => 60,
                'hair_color' => 'castanho',
                'eye_color' => 'castanho',
                'ethnicity' => 'parda',
                'has_tattoos' => false,
                'has_piercings' => true,
                'has_silicone' => true,
                'is_smoker' => true,
                'attends_home' => false,
                'travel_radius_km' => 15,
                'whatsapp' => '(11) 92222-2222',
                'telegram' => '@bianca_ferreira',
            ],
            [
                'name' => 'Carolina Lima',
                'email' => 'travesti3@teste.com',
                'artistic_name' => 'Carolina',
                'age' => 23,
                'about_me' => 'Travesti jovem e vibrante. Especialista em serviços personalizados e companhia para eventos especiais.',
                'height' => 168,
                'weight' => 58,
                'hair_color' => 'ruivo',
                'eye_color' => 'verde',
                'ethnicity' => 'branca',
                'has_tattoos' => true,
                'has_piercings' => false,
                'has_silicone' => false,
                'is_smoker' => false,
                'attends_home' => true,
                'travel_radius_km' => 25,
                'whatsapp' => '(11) 93333-3333',
                'telegram' => '@carolina_lima',
            ],
        ];

        foreach ($transvestites as $index => $data) {
            $district = $districts[$index % $districts->count()];

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'user_type' => 'transvestite',
                    'phone' => $data['whatsapp'],
                    'active' => true,
                    'email_verified_at' => now(),
                    'cep' => '01234-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'address' => 'Rua das Flores, ' . ($index + 100),
                    'complement' => 'Apto ' . ($index + 10),
                    'state_id' => $state->id,
                    'city_id' => $city->id,
                    'district_id' => $district->id,
                ]
            );

            if (!$user->companionProfile) {
                CompanionProfile::create([
                    'user_id' => $user->id,
                    'artistic_name' => $data['artistic_name'],
                    'age' => $data['age'],
                    'about_me' => $data['about_me'],
                    'height' => $data['height'],
                    'weight' => $data['weight'],
                    'hair_color' => $data['hair_color'],
                    'eye_color' => $data['eye_color'],
                    'ethnicity' => $data['ethnicity'],
                    'has_tattoos' => $data['has_tattoos'],
                    'has_piercings' => $data['has_piercings'],
                    'has_silicone' => $data['has_silicone'],
                    'is_smoker' => $data['is_smoker'],
                    'verified' => true,
                    'verification_date' => now(),
                    'online_status' => rand(0, 1) == 1,
                    'last_active' => now(),
                    'plan_id' => $basicPlan?->id,
                    'plan_expires_at' => $basicPlan ? now()->addDays(30) : null,
                    'city_id' => $city->id,
                    'attends_home' => $data['attends_home'],
                    'travel_radius_km' => $data['travel_radius_km'],
                    'whatsapp' => $data['whatsapp'],
                    'telegram' => $data['telegram'],
                ]);
            }

            echo "✅ Travesti criada: {$data['artistic_name']} ({$data['email']})\n";
        }
    }

    private function createMaleEscorts(City $city, State $state, $districts, $basicPlan): void
    {
        $maleEscorts = [
            [
                'name' => 'Rafael Costa',
                'email' => 'garoto1@teste.com',
                'artistic_name' => 'Rafael',
                'age' => 26,
                'about_me' => 'Garoto de programa atlético e profissional. Ofereço serviços de qualidade com foco na satisfação total.',
                'height' => 180,
                'weight' => 75,
                'hair_color' => 'castanho',
                'eye_color' => 'castanho',
                'ethnicity' => 'branco',
                'has_tattoos' => true,
                'has_piercings' => false,
                'has_silicone' => false,
                'is_smoker' => false,
                'attends_home' => true,
                'travel_radius_km' => 30,
                'whatsapp' => '(11) 94444-4444',
                'telegram' => '@rafael_costa',
            ],
            [
                'name' => 'Lucas Silva',
                'email' => 'garoto2@teste.com',
                'artistic_name' => 'Lucas',
                'age' => 24,
                'about_me' => 'Garoto de programa jovem e charmoso. Especialista em massagens e companhia para eventos especiais.',
                'height' => 175,
                'weight' => 70,
                'hair_color' => 'loiro',
                'eye_color' => 'azul',
                'ethnicity' => 'branco',
                'has_tattoos' => false,
                'has_piercings' => true,
                'has_silicone' => false,
                'is_smoker' => true,
                'attends_home' => false,
                'travel_radius_km' => 20,
                'whatsapp' => '(11) 95555-5555',
                'telegram' => '@lucas_silva',
            ],
            [
                'name' => 'Diego Santos',
                'email' => 'garoto3@teste.com',
                'artistic_name' => 'Diego',
                'age' => 29,
                'about_me' => 'Garoto de programa experiente e discreto. Ofereço serviços personalizados com total profissionalismo.',
                'height' => 182,
                'weight' => 78,
                'hair_color' => 'preto',
                'eye_color' => 'castanho',
                'ethnicity' => 'pardo',
                'has_tattoos' => true,
                'has_piercings' => true,
                'has_silicone' => false,
                'is_smoker' => false,
                'attends_home' => true,
                'travel_radius_km' => 25,
                'whatsapp' => '(11) 96666-6666',
                'telegram' => '@diego_santos',
            ],
        ];

        foreach ($maleEscorts as $index => $data) {
            $district = $districts[$index % $districts->count()];

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'user_type' => 'male_escort',
                    'phone' => $data['whatsapp'],
                    'active' => true,
                    'email_verified_at' => now(),
                    'cep' => '01234-' . str_pad($index + 4, 3, '0', STR_PAD_LEFT),
                    'address' => 'Rua dos Garotos, ' . ($index + 200),
                    'complement' => 'Casa ' . ($index + 20),
                    'state_id' => $state->id,
                    'city_id' => $city->id,
                    'district_id' => $district->id,
                ]
            );

            if (!$user->companionProfile) {
                CompanionProfile::create([
                    'user_id' => $user->id,
                    'artistic_name' => $data['artistic_name'],
                    'age' => $data['age'],
                    'about_me' => $data['about_me'],
                    'height' => $data['height'],
                    'weight' => $data['weight'],
                    'hair_color' => $data['hair_color'],
                    'eye_color' => $data['eye_color'],
                    'ethnicity' => $data['ethnicity'],
                    'has_tattoos' => $data['has_tattoos'],
                    'has_piercings' => $data['has_piercings'],
                    'has_silicone' => $data['has_silicone'],
                    'is_smoker' => $data['is_smoker'],
                    'verified' => true,
                    'verification_date' => now(),
                    'online_status' => rand(0, 1) == 1,
                    'last_active' => now(),
                    'plan_id' => $basicPlan?->id,
                    'plan_expires_at' => $basicPlan ? now()->addDays(30) : null,
                    'city_id' => $city->id,
                    'attends_home' => $data['attends_home'],
                    'travel_radius_km' => $data['travel_radius_km'],
                    'whatsapp' => $data['whatsapp'],
                    'telegram' => $data['telegram'],
                ]);
            }

            echo "✅ Garoto de programa criado: {$data['artistic_name']} ({$data['email']})\n";
        }
    }
}
