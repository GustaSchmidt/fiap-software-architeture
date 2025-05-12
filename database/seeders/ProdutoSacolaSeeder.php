<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProdutoSacola;
use App\Models\Product;
use App\Models\Sacola;

class ProdutoSacolaSeeder extends Seeder
{
    public function run(): void
    {
        ProdutoSacola::create([
            'sacola_id' => 1,
            'produto_id' => 1,
            'quantidade' => 2,
        ]);

        ProdutoSacola::create([
            'sacola_id' => 2,
            'produto_id' => 2,
            'quantidade' => 1,
        ]);

        // Atualizar o total das sacolas
        $sacola1 = Sacola::find(1);
        $sacola1->total = Product::find(1)->preco * 2;
        $sacola1->save();

        $sacola2 = Sacola::find(2);
        $sacola2->total = Product::find(2)->preco * 1;
        $sacola2->save();
    }
}
