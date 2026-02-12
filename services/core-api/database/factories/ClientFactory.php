<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class ClientFactory extends Factory
{
    /**
     * O nome da model correspondente a esta factory.
     */
    protected $model = Client::class;

    /**
     * Define o estado padrão do modelo.
     */
    public function definition()
    {
        return [
            'nome' => $this->faker->firstName(),
            'sobrenome' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'cpf' => $this->faker->numerify('###########'), // 11 dígitos
            'senha' => Hash::make('password'),
        ];
    }
}