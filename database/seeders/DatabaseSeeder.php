<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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

        // Criação de dados de exemplo (apenas em desenvolvimento)
        if (app()->environment('local', 'development')) {
            $this->createSampleData();
        }
    }

    /**
     * Criar dados de exemplo para desenvolvimento
     */
    private function createSampleData(): void
    {
        // Buscar estados existentes
        $sp = \App\Models\State::where('uf', 'SP')->first();
        $rj = \App\Models\State::where('uf', 'RJ')->first();
        $mg = \App\Models\State::where('uf', 'MG')->first();

        if ($sp) {
            // Criar cidades de exemplo para SP
            $cities = [
                ['name' => 'São Paulo', 'state_id' => $sp->id],
                ['name' => 'Santos', 'state_id' => $sp->id],
                ['name' => 'Campinas', 'state_id' => $sp->id],
            ];

            foreach ($cities as $cityData) {
                $city = City::firstOrCreate($cityData);

                // Criar bairros de exemplo
                $districts = [
                    ['name' => 'Centro', 'city_id' => $city->id],
                    ['name' => 'Vila Madalena', 'city_id' => $city->id],
                    ['name' => 'Pinheiros', 'city_id' => $city->id],
                    ['name' => 'Itaim Bibi', 'city_id' => $city->id],
                ];

                foreach ($districts as $districtData) {
                    District::firstOrCreate($districtData);
                }
            }
        }

        if ($rj) {
            // Criar cidades de exemplo para RJ
            $cities = [
                ['name' => 'Rio de Janeiro', 'state_id' => $rj->id],
                ['name' => 'Niterói', 'state_id' => $rj->id],
            ];

            foreach ($cities as $cityData) {
                $city = City::firstOrCreate($cityData);

                $districts = [
                    ['name' => 'Copacabana', 'city_id' => $city->id],
                    ['name' => 'Ipanema', 'city_id' => $city->id],
                    ['name' => 'Leblon', 'city_id' => $city->id],
                ];

                foreach ($districts as $districtData) {
                    District::firstOrCreate($districtData);
                }
            }
        }

        if ($mg) {
            // Criar cidades de exemplo para MG
            $cities = [
                ['name' => 'Belo Horizonte', 'state_id' => $mg->id],
                ['name' => 'Uberlândia', 'state_id' => $mg->id],
            ];

            foreach ($cities as $cityData) {
                $city = City::firstOrCreate($cityData);

                $districts = [
                    ['name' => 'Centro', 'city_id' => $city->id],
                    ['name' => 'Savassi', 'city_id' => $city->id],
                ];

                foreach ($districts as $districtData) {
                    District::firstOrCreate($districtData);
                }
            }
        }
    }
}
