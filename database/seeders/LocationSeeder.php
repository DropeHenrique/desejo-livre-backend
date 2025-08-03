<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\City;
use App\Models\District;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar estados
        $sp = State::create([
            'name' => 'São Paulo',
            'uf' => 'SP',
            'slug' => 'sao-paulo',
        ]);

        $rj = State::create([
            'name' => 'Rio de Janeiro',
            'uf' => 'RJ',
            'slug' => 'rio-de-janeiro',
        ]);

        $mg = State::create([
            'name' => 'Minas Gerais',
            'uf' => 'MG',
            'slug' => 'minas-gerais',
        ]);

        // Criar cidades
        $saoPaulo = City::create([
            'name' => 'São Paulo',
            'slug' => 'sao-paulo',
            'state_id' => $sp->id,
        ]);

        $santos = City::create([
            'name' => 'Santos',
            'slug' => 'santos',
            'state_id' => $sp->id,
        ]);

        $rioDeJaneiro = City::create([
            'name' => 'Rio de Janeiro',
            'slug' => 'rio-de-janeiro',
            'state_id' => $rj->id,
        ]);

        $beloHorizonte = City::create([
            'name' => 'Belo Horizonte',
            'slug' => 'belo-horizonte',
            'state_id' => $mg->id,
        ]);

        // Criar bairros
        District::create([
            'name' => 'Vila Madalena',
            'slug' => 'vila-madalena',
            'city_id' => $saoPaulo->id,
        ]);

        District::create([
            'name' => 'Pinheiros',
            'slug' => 'pinheiros',
            'city_id' => $saoPaulo->id,
        ]);

        District::create([
            'name' => 'Copacabana',
            'slug' => 'copacabana',
            'city_id' => $rioDeJaneiro->id,
        ]);

        District::create([
            'name' => 'Ipanema',
            'slug' => 'ipanema',
            'city_id' => $rioDeJaneiro->id,
        ]);

        District::create([
            'name' => 'Savassi',
            'slug' => 'savassi',
            'city_id' => $beloHorizonte->id,
        ]);

        District::create([
            'name' => 'Gonzaga',
            'slug' => 'gonzaga',
            'city_id' => $santos->id,
        ]);
    }
}
