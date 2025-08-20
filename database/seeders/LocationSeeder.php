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
        // Criar estados usando firstOrCreate para evitar duplicação
        $sp = State::firstOrCreate(
            ['uf' => 'SP'],
            [
                'name' => 'São Paulo',
                'uf' => 'SP',
                'slug' => 'sao-paulo',
            ]
        );

        $rj = State::firstOrCreate(
            ['uf' => 'RJ'],
            [
                'name' => 'Rio de Janeiro',
                'uf' => 'RJ',
                'slug' => 'rio-de-janeiro',
            ]
        );

        $mg = State::firstOrCreate(
            ['uf' => 'MG'],
            [
                'name' => 'Minas Gerais',
                'uf' => 'MG',
                'slug' => 'minas-gerais',
            ]
        );

        // Criar cidades usando firstOrCreate
        $saoPaulo = City::firstOrCreate(
            ['name' => 'São Paulo', 'state_id' => $sp->id],
            [
                'name' => 'São Paulo',
                'slug' => 'sao-paulo',
                'state_id' => $sp->id,
            ]
        );

        $santos = City::firstOrCreate(
            ['name' => 'Santos', 'state_id' => $sp->id],
            [
                'name' => 'Santos',
                'slug' => 'santos',
                'state_id' => $sp->id,
            ]
        );

        $rioDeJaneiro = City::firstOrCreate(
            ['name' => 'Rio de Janeiro', 'state_id' => $rj->id],
            [
                'name' => 'Rio de Janeiro',
                'slug' => 'rio-de-janeiro',
                'state_id' => $rj->id,
            ]
        );

        $beloHorizonte = City::firstOrCreate(
            ['name' => 'Belo Horizonte', 'state_id' => $mg->id],
            [
                'name' => 'Belo Horizonte',
                'slug' => 'belo-horizonte',
                'state_id' => $mg->id,
            ]
        );

        // Criar bairros usando firstOrCreate
        District::firstOrCreate(
            ['name' => 'Centro', 'city_id' => $saoPaulo->id],
            [
                'name' => 'Centro',
                'slug' => 'centro',
                'city_id' => $saoPaulo->id,
            ]
        );

        District::firstOrCreate(
            ['name' => 'Vila Madalena', 'city_id' => $saoPaulo->id],
            [
                'name' => 'Vila Madalena',
                'slug' => 'vila-madalena',
                'city_id' => $saoPaulo->id,
            ]
        );

        District::firstOrCreate(
            ['name' => 'Pinheiros', 'city_id' => $saoPaulo->id],
            [
                'name' => 'Pinheiros',
                'slug' => 'pinheiros',
                'city_id' => $saoPaulo->id,
            ]
        );

        District::firstOrCreate(
            ['name' => 'Jardins', 'city_id' => $saoPaulo->id],
            [
                'name' => 'Jardins',
                'slug' => 'jardins',
                'city_id' => $saoPaulo->id,
            ]
        );

        District::firstOrCreate(
            ['name' => 'Itaim Bibi', 'city_id' => $saoPaulo->id],
            [
                'name' => 'Itaim Bibi',
                'slug' => 'itaim-bibi',
                'city_id' => $saoPaulo->id,
            ]
        );

        District::firstOrCreate(
            ['name' => 'Copacabana', 'city_id' => $rioDeJaneiro->id],
            [
                'name' => 'Copacabana',
                'slug' => 'copacabana',
                'city_id' => $rioDeJaneiro->id,
            ]
        );

        District::firstOrCreate(
            ['name' => 'Ipanema', 'city_id' => $rioDeJaneiro->id],
            [
                'name' => 'Ipanema',
                'slug' => 'ipanema',
                'city_id' => $rioDeJaneiro->id,
            ]
        );

        District::firstOrCreate(
            ['name' => 'Leblon', 'city_id' => $rioDeJaneiro->id],
            [
                'name' => 'Leblon',
                'slug' => 'leblon',
                'city_id' => $rioDeJaneiro->id,
            ]
        );

        District::firstOrCreate(
            ['name' => 'Savassi', 'city_id' => $beloHorizonte->id],
            [
                'name' => 'Savassi',
                'slug' => 'savassi',
                'city_id' => $beloHorizonte->id,
            ]
        );

        District::firstOrCreate(
            ['name' => 'Centro', 'city_id' => $beloHorizonte->id],
            [
                'name' => 'Centro',
                'slug' => 'centro-bh',
                'city_id' => $beloHorizonte->id,
            ]
        );

        District::firstOrCreate(
            ['name' => 'Gonzaga', 'city_id' => $santos->id],
            [
                'name' => 'Gonzaga',
                'slug' => 'gonzaga',
                'city_id' => $santos->id,
            ]
        );

        District::firstOrCreate(
            ['name' => 'Centro', 'city_id' => $santos->id],
            [
                'name' => 'Centro',
                'slug' => 'centro-santos',
                'city_id' => $santos->id,
            ]
        );
    }
}
