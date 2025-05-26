<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\ApiKey;

class ApiKeysTableSeeder extends Seeder
{
    public function run(): void
    {
        ApiKey::insert([
            [
                'name' => 'Admin Key',
                'key' => Str::uuid(),
                'role' => 'admin',
                'role_id_loja_cliente' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Loja XPTO',
                'key' => Str::uuid(),
                'role' => 'loja',
                'role_id_loja_cliente' => 1, // id da loja criada tambem com seeder
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cliente Fulano',
                'key' => Str::uuid(),
                'role' => 'cliente',
                'role_id_loja_cliente' => 1, // id do cliente tambem com seeder
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

