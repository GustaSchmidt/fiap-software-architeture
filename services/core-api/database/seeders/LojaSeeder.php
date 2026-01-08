<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Loja;

class LojaSeeder extends Seeder
{
    public function run(): void
    {
        Loja::create(['nome' => 'Padaria do Zé', 'endereco' => 'Rua A, 123']);
        Loja::create(['nome' => 'Confeitaria da Ana', 'endereco' => 'Av. Central, 456']);
    }
}
