<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sacola;

class SacolaSeeder extends Seeder
{
    public function run(): void
    {
        Sacola::create([
            'cliente_id' => 1,
            'status' => 'aberta',
            'total' => 0,
        ]);

        Sacola::create([
            'cliente_id' => 2,
            'status' => 'aberta',
            'total' => 0,
        ]);
    }
}
