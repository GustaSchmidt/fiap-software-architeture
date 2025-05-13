<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Sacola;

class ProdutoSacolaSeeder extends Seeder
{
    public function run(): void
    {
        // Sacola 1 recebe Produto 1 (quantidade 2)
        $sacola1 = Sacola::find(1);
        $produto1 = Product::find(1);

        if ($sacola1 && $produto1) {
            $sacola1->products()->attach($produto1->id, ['quantidade' => 2]);

            $sacola1->total = $produto1->preco * 2;
            $sacola1->save();
        }

        // Sacola 2 recebe Produto 2 (quantidade 1)
        $sacola2 = Sacola::find(2);
        $produto2 = Product::find(2);

        if ($sacola2 && $produto2) {
            $sacola2->products()->attach($produto2->id, ['quantidade' => 1]);

            $sacola2->total = $produto2->preco * 1;
            $sacola2->save();
        }
    }
}
