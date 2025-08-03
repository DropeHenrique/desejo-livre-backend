<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceType;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando tipos de serviço...');

        $serviceTypes = [
            [
                'name' => 'Massagem',
                'description' => 'Massagens relaxantes e terapêuticas',
                'icon' => 'spa',
                'active' => true,
            ],
            [
                'name' => 'Companhia',
                'description' => 'Acompanhamento para eventos e encontros',
                'icon' => 'heart',
                'active' => true,
            ],
            [
                'name' => 'Jantar',
                'description' => 'Acompanhamento para jantares e restaurantes',
                'icon' => 'utensils',
                'active' => true,
            ],
            [
                'name' => 'Viagem',
                'description' => 'Acompanhamento para viagens e passeios',
                'icon' => 'plane',
                'active' => true,
            ],
            [
                'name' => 'Fantasias',
                'description' => 'Realização de fantasias e fetiches',
                'icon' => 'star',
                'active' => true,
            ],
            [
                'name' => 'Dupla',
                'description' => 'Serviços em dupla com outra acompanhante',
                'icon' => 'users',
                'active' => true,
            ],
            [
                'name' => 'Fetiches',
                'description' => 'Serviços especializados em fetiches',
                'icon' => 'gem',
                'active' => true,
            ],
            [
                'name' => 'Fotos',
                'description' => 'Sessões de fotos profissionais',
                'icon' => 'camera',
                'active' => true,
            ],
        ];

        foreach ($serviceTypes as $serviceType) {
            ServiceType::firstOrCreate(
                ['name' => $serviceType['name']],
                $serviceType
            );
        }

        $this->command->info('✅ ' . ServiceType::count() . ' tipos de serviço criados com sucesso!');
    }
}
