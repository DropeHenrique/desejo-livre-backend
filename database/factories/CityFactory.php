<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CityFactory extends Factory
{
    protected $model = City::class;

    private static $brazilianCities = [
        'São Paulo', 'Rio de Janeiro', 'Brasília', 'Salvador', 'Fortaleza',
        'Belo Horizonte', 'Manaus', 'Curitiba', 'Recife', 'Goiânia',
        'Belém', 'Porto Alegre', 'Guarulhos', 'Campinas', 'São Luís',
        'São Gonçalo', 'Maceió', 'Duque de Caxias', 'Natal', 'Teresina',
        'Campo Grande', 'Nova Iguaçu', 'São Bernardo do Campo', 'João Pessoa',
        'Santo André', 'Osasco', 'Jaboatão dos Guararapes', 'São José dos Campos',
        'Ribeirão Preto', 'Uberlândia', 'Contagem', 'Sorocaba', 'Aracaju',
        'Feira de Santana', 'Cuiabá', 'Joinville', 'Juiz de Fora', 'Londrina',
        'Niterói', 'Ananindeua', 'Belford Roxo', 'Campos dos Goytacazes',
        'Santos', 'Bauru', 'Caxias do Sul', 'Vila Velha', 'Serra',
        'Cariacica', 'Vitória', 'Florianópolis', 'Maringá', 'Aparecida de Goiânia'
    ];

    public function definition(): array
    {
        $name = $this->faker->randomElement(self::$brazilianCities);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'state_id' => State::factory(),
        ];
    }
}
