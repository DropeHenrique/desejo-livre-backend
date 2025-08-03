<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanionProfile;
use App\Models\District;
use Illuminate\Support\Facades\DB;

class CompanionDistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando relacionamentos companion_districts...');

        $companions = CompanionProfile::all();
        $districts = District::all();

        if ($companions->isEmpty()) {
            $this->command->error('Nenhum acompanhante encontrado. Execute o CompanionProfileSeeder primeiro.');
            return;
        }

        if ($districts->isEmpty()) {
            $this->command->error('Nenhum bairro encontrado. Execute o DatabaseSeeder primeiro.');
            return;
        }

        $createdCount = 0;

        foreach ($companions as $companion) {
            // Cada acompanhante pode atender em 1-3 bairros da sua cidade
            $cityDistricts = $districts->where('city_id', $companion->city_id);

            if ($cityDistricts->count() > 0) {
                $numDistricts = rand(1, min(3, $cityDistricts->count()));
                $selectedDistricts = $cityDistricts->random($numDistricts);

                foreach ($selectedDistricts as $district) {
                    // Verificar se já existe o relacionamento
                    $exists = DB::table('companion_districts')
                        ->where('companion_profile_id', $companion->id)
                        ->where('district_id', $district->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('companion_districts')->insert([
                            'companion_profile_id' => $companion->id,
                            'district_id' => $district->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $createdCount++;
                    }
                }
            }
        }

        $this->command->info("✅ {$createdCount} relacionamentos companion_districts criados com sucesso!");

        // Estatísticas
        $totalRelationships = DB::table('companion_districts')->count();
        $companionsWithDistricts = DB::table('companion_districts')
            ->distinct('companion_profile_id')
            ->count('companion_profile_id');

        $this->command->info("📊 Total de relacionamentos: {$totalRelationships}");
        $this->command->info("👥 Acompanhantes com bairros: {$companionsWithDistricts}");
    }
}
