<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    /**
     * Planos para acompanhantes
     */
    private array $companionPlans = [
        [
            'name' => 'Bronze',
            'price' => 29.90,
            'duration_days' => 30,
            'description' => 'Plano básico para acompanhantes iniciantes',
            'features' => ['Perfil básico', 'Upload de fotos']
        ],
        [
            'name' => 'Prata',
            'price' => 49.90,
            'duration_days' => 30,
            'description' => 'Plano intermediário com recursos de destaque',
            'features' => ['Perfil básico', 'Upload de fotos', 'Destaque na busca', 'Até 10 fotos']
        ],
        [
            'name' => 'Ouro',
            'price' => 79.90,
            'duration_days' => 30,
            'description' => 'Plano premium com recursos avançados',
            'features' => ['Perfil básico', 'Upload de fotos', 'Destaque premium', 'Até 20 fotos', 'Upload de vídeos']
        ],
        [
            'name' => 'Black',
            'price' => 129.90,
            'duration_days' => 30,
            'description' => 'Plano VIP com todos os recursos disponíveis',
            'features' => ['Perfil básico', 'Upload de fotos', 'Máximo destaque', 'Fotos ilimitadas', 'Vídeos ilimitados', 'Suporte prioritário']
        ],
    ];

    /**
     * Planos para clientes
     */
    private array $clientPlans = [
        [
            'name' => 'Básico',
            'price' => 9.90,
            'duration_days' => 30,
            'description' => 'Plano básico para clientes',
            'features' => ['Busca básica', 'Visualizar perfis']
        ],
        [
            'name' => 'Premium',
            'price' => 19.90,
            'duration_days' => 30,
            'description' => 'Plano premium com recursos avançados',
            'features' => ['Busca básica', 'Visualizar perfis', 'Filtros avançados', 'Favoritos ilimitados']
        ],
        [
            'name' => 'VIP',
            'price' => 39.90,
            'duration_days' => 30,
            'description' => 'Plano VIP com acesso completo',
            'features' => ['Busca básica', 'Visualizar perfis', 'Filtros avançados', 'Favoritos ilimitados', 'Acesso prioritário', 'Chat direto', 'Sem anúncios']
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar planos existentes
        DB::table('plans')->truncate();

        $now = now();

        // Criar planos para acompanhantes
        foreach ($this->companionPlans as $planData) {
            DB::table('plans')->insert([
                'name' => $planData['name'],
                'slug' => Str::slug($planData['name'] . '-companion'),
                'price' => $planData['price'],
                'duration_days' => $planData['duration_days'],
                'description' => $planData['description'],
                'features' => json_encode($planData['features']),
                'user_type' => 'companion',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Criar planos para clientes
        foreach ($this->clientPlans as $planData) {
            DB::table('plans')->insert([
                'name' => $planData['name'],
                'slug' => Str::slug($planData['name'] . '-client'),
                'price' => $planData['price'],
                'duration_days' => $planData['duration_days'],
                'description' => $planData['description'],
                'features' => json_encode($planData['features']),
                'user_type' => 'client',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Planos criados com sucesso!');
    }
}
