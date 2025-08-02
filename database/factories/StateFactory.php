<?php

namespace Database\Factories;

use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StateFactory extends Factory
{
    protected $model = State::class;

    private static $brazilianStates = [
        ['name' => 'Acre', 'uf' => 'AC'],
        ['name' => 'Alagoas', 'uf' => 'AL'],
        ['name' => 'Amapá', 'uf' => 'AP'],
        ['name' => 'Amazonas', 'uf' => 'AM'],
        ['name' => 'Bahia', 'uf' => 'BA'],
        ['name' => 'Ceará', 'uf' => 'CE'],
        ['name' => 'Distrito Federal', 'uf' => 'DF'],
        ['name' => 'Espírito Santo', 'uf' => 'ES'],
        ['name' => 'Goiás', 'uf' => 'GO'],
        ['name' => 'Maranhão', 'uf' => 'MA'],
        ['name' => 'Mato Grosso', 'uf' => 'MT'],
        ['name' => 'Mato Grosso do Sul', 'uf' => 'MS'],
        ['name' => 'Minas Gerais', 'uf' => 'MG'],
        ['name' => 'Pará', 'uf' => 'PA'],
        ['name' => 'Paraíba', 'uf' => 'PB'],
        ['name' => 'Paraná', 'uf' => 'PR'],
        ['name' => 'Pernambuco', 'uf' => 'PE'],
        ['name' => 'Piauí', 'uf' => 'PI'],
        ['name' => 'Rio de Janeiro', 'uf' => 'RJ'],
        ['name' => 'Rio Grande do Norte', 'uf' => 'RN'],
        ['name' => 'Rio Grande do Sul', 'uf' => 'RS'],
        ['name' => 'Rondônia', 'uf' => 'RO'],
        ['name' => 'Roraima', 'uf' => 'RR'],
        ['name' => 'Santa Catarina', 'uf' => 'SC'],
        ['name' => 'São Paulo', 'uf' => 'SP'],
        ['name' => 'Sergipe', 'uf' => 'SE'],
        ['name' => 'Tocantins', 'uf' => 'TO'],
    ];

    public function definition(): array
    {
        static $counter = 0;
        $total = count(self::$brazilianStates);

        if ($counter < $total) {
            $state = self::$brazilianStates[$counter];
            $counter++;
            return [
                'name' => $state['name'],
                'uf' => $state['uf'],
                'slug' => Str::slug($state['name']),
            ];
        }

        $name = 'Estado ' . $counter;
        // Usar uma sequência numérica simples para UF quando não há mais estados brasileiros
        // Usar números que não conflitem com UFs reais (AC, AL, AP, etc.)
        $uf = 'X' . ($counter - 26); // X1, X2, X3, ... (começa após os 27 estados brasileiros)
        $counter++;
        return [
            'name' => $name,
            'uf' => $uf,
            'slug' => Str::slug($name),
        ];
    }
}
