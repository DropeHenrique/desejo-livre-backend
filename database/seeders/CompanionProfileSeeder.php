<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanionProfile;
use App\Models\City;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompanionProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar planos de acompanhante
        $plans = Plan::where('user_type', 'companion')->get();

        if ($plans->isEmpty()) {
            $this->command->error('Nenhum plano de acompanhante encontrado. Execute o PlanSeeder primeiro.');
            return;
        }

        // Buscar cidades existentes
        $cities = City::all();

        if ($cities->isEmpty()) {
            $this->command->error('Nenhuma cidade encontrada. Execute o DatabaseSeeder primeiro.');
            return;
        }

        $this->command->info('Criando acompanhantes de exemplo...');

        $artisticNames = [
            'Luna', 'Sofia', 'Isabella', 'Valentina', 'Camila', 'Gabriela',
            'Nicole', 'Mariana', 'Beatriz', 'Amanda', 'Rafaela', 'Letícia',
            'Fernanda', 'Juliana', 'Carolina', 'Larissa', 'Vanessa', 'Patrícia',
            'Natália', 'Débora', 'Priscila', 'Mônica', 'Adriana', 'Cristina',
            'Sabrina', 'Tatiana', 'Renata', 'Elaine', 'Simone', 'Luciana'
        ];

        $eyeColors = ['castanhos', 'verdes', 'azuis', 'pretos', 'mel', 'amendoados'];
        $hairColors = ['loiro', 'moreno', 'ruivo', 'preto', 'castanho', 'platinado'];
        $ethnicities = ['branca', 'morena', 'negra', 'asiática', 'indígena', 'mulata'];

        // Criar 50 acompanhantes distribuídos pelas cidades
        for ($i = 0; $i < 50; $i++) {
            $city = $cities->random();
            $plan = $plans->random();
            $timestamp = time();

            // Criar usuário para o acompanhante
            $user = User::create([
                'name' => fake()->name(),
                'email' => "companion{$i}_{$timestamp}@example.com",
                'password' => bcrypt('password'),
                'user_type' => 'companion',
                'phone' => fake()->phoneNumber(),
                'active' => true,
            ]);

            // Criar perfil do acompanhante
            CompanionProfile::create([
                'user_id' => $user->id,
                'artistic_name' => $artisticNames[array_rand($artisticNames)],
                'slug' => 'companion-' . $i . '-' . $timestamp,
                'age' => rand(18, 40),
                'hide_age' => rand(0, 100) < 30,
                'about_me' => fake()->paragraph(3),
                'height' => rand(150, 180),
                'weight' => rand(45, 80),
                'eye_color' => $eyeColors[array_rand($eyeColors)],
                'hair_color' => $hairColors[array_rand($hairColors)],
                'ethnicity' => $ethnicities[array_rand($ethnicities)],
                'has_tattoos' => rand(0, 100) < 40,
                'has_piercings' => rand(0, 100) < 30,
                'has_silicone' => rand(0, 100) < 25,
                'is_smoker' => rand(0, 100) < 20,
                'verified' => rand(0, 100) < 70,
                'verification_date' => rand(0, 100) < 70 ? now() : null,
                'online_status' => rand(0, 100) < 40,
                'last_active' => now(),
                'plan_id' => $plan->id,
                'plan_expires_at' => now()->addMonths(rand(1, 6)),
                'city_id' => $city->id,
                'whatsapp' => fake()->phoneNumber(),
                'telegram' => '@' . fake()->userName(),
            ]);
        }

        // Criar alguns acompanhantes em destaque (verificados e online)
        for ($i = 0; $i < 10; $i++) {
            $city = $cities->random();
            $plan = $plans->random();
            $timestamp = time();

            $user = User::create([
                'name' => fake()->name(),
                'email' => "featured{$i}_{$timestamp}@example.com",
                'password' => bcrypt('password'),
                'user_type' => 'companion',
                'phone' => fake()->phoneNumber(),
                'active' => true,
            ]);

            CompanionProfile::create([
                'user_id' => $user->id,
                'artistic_name' => $artisticNames[array_rand($artisticNames)],
                'slug' => 'featured-' . $i . '-' . $timestamp,
                'age' => rand(18, 40),
                'hide_age' => rand(0, 100) < 30,
                'about_me' => fake()->paragraph(3),
                'height' => rand(150, 180),
                'weight' => rand(45, 80),
                'eye_color' => $eyeColors[array_rand($eyeColors)],
                'hair_color' => $hairColors[array_rand($hairColors)],
                'ethnicity' => $ethnicities[array_rand($ethnicities)],
                'has_tattoos' => rand(0, 100) < 40,
                'has_piercings' => rand(0, 100) < 30,
                'has_silicone' => rand(0, 100) < 25,
                'is_smoker' => rand(0, 100) < 20,
                'verified' => true,
                'verification_date' => now(),
                'online_status' => true,
                'last_active' => now(),
                'plan_id' => $plan->id,
                'plan_expires_at' => now()->addMonths(rand(3, 12)),
                'city_id' => $city->id,
                'whatsapp' => fake()->phoneNumber(),
                'telegram' => '@' . fake()->userName(),
            ]);
        }

        $this->command->info('✅ ' . CompanionProfile::count() . ' acompanhantes criados com sucesso!');
        $this->command->info('📊 Distribuição por cidade:');

        $cityStats = DB::table('companion_profiles')
            ->join('cities', 'companion_profiles.city_id', '=', 'cities.id')
            ->select('cities.name', DB::raw('count(*) as total'))
            ->groupBy('cities.id', 'cities.name')
            ->orderBy('total', 'desc')
            ->get();

        foreach ($cityStats as $stat) {
            $this->command->info("   - {$stat->name}: {$stat->total} acompanhantes");
        }

        $verifiedCount = CompanionProfile::where('verified', true)->count();
        $onlineCount = CompanionProfile::where('online_status', true)->count();

        $this->command->info("✅ {$verifiedCount} acompanhantes verificados");
        $this->command->info("🟢 {$onlineCount} acompanhantes online");
    }
}
