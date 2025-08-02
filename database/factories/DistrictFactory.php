<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistrictFactory extends Factory
{
    protected $model = District::class;

    private static $districtNames = [
        'Centro', 'Vila Madalena', 'Copacabana', 'Ipanema', 'Jardins',
        'Leblon', 'Bela Vista', 'Liberdade', 'Santa Cecília', 'Vila Olímpia',
        'Moema', 'Vila Nova Conceição', 'Higienópolis', 'Perdizes', 'Pinheiros',
        'Vila Mariana', 'Saúde', 'Brooklin', 'Campo Belo', 'Chácara Klabin',
        'Vila Madalena', 'Vila Pompeia', 'Lapa', 'Barra Funda', 'Santa Efigênia',
        'República', 'Consolação', 'Vila Buarque', 'Aclimação', 'Paraíso',
        'Ibirapuera', 'Mooca', 'Brás', 'Belém', 'Tatuapé',
        'Vila Prudente', 'Ipiranga', 'Sacomã', 'Cursino', 'Vila Clementino',
        'Campo Grande', 'Santo Amaro', 'Socorro', 'Cidade Ademar', 'Jabaquara',
        'Vila Andrade', 'Morumbi', 'Butantã', 'Rio Pequeno', 'Raposo Tavares'
    ];

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(self::$districtNames),
            'city_id' => City::factory(),
        ];
    }
}
