<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\City;
use App\Models\District;
use App\Models\Plan;
use App\Models\CompanionProfile;
use Illuminate\Support\Facades\Config;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Desabilitar Scout durante o seeding para evitar erros do Algolia
        $originalScoutDriver = Config::get('scout.driver');
        Config::set('scout.driver', 'null');

        // Seeders básicos que devem ser executados sempre (dados fundamentais)
        $this->call([
            StateSeeder::class,
            LocationSeeder::class, // Adicionando LocationSeeder
            PlanSeeder::class,
            ServiceTypeSeeder::class,
            AdminUserSeeder::class, // Admin deve ser criado primeiro
        ]);

        // Seeders de conteúdo e usuários
        $this->call([
            BlogSeeder::class,
            TestUsersSeeder::class,
            TransvestiteMaleEscortSeeder::class,
        ]);

        // Criação de dados de exemplo (apenas em desenvolvimento)
        if (app()->environment('local', 'development')) {
            $this->createSampleData();

            // Seeders de dados de exemplo para desenvolvimento
            $this->call([
                SampleDataSeeder::class, // Adicionando SampleDataSeeder
                CompanionProfileSeeder::class,
                CompanionDistrictSeeder::class,
                CompanionServiceSeeder::class,
                MediaSeeder::class,
                TestSubscriptionSeeder::class, // Adicionando TestSubscriptionSeeder
            ]);
        }

        // Restaurar configuração original do Scout
        Config::set('scout.driver', $originalScoutDriver);
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
                    ['name' => 'Pampulha', 'city_id' => $city->id],
                ];

                foreach ($districts as $districtData) {
                    District::firstOrCreate($districtData);
                }
            }
        }
    }
}
