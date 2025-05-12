<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::create([
            'nome' => 'João',
            'sobrenome' => 'Silva',
            'email' => 'joao@example.com',
            'cpf' => '404.562.410-43',
            'senha' => md5('senha123'),
        ]);
        Client::create([
            'nome' => 'Maria',
            'sobrenome' => 'Oliveira',
            'email' => 'maria@example.com',
            'cpf' => '926.420.220-05',
            'senha' => md5('senha456'),
        ]);
    }
}

