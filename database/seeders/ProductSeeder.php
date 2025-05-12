<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'nome' => 'Pão Francês',
            'descricao' => 'Pão crocante e fresco.',
            'preco' => 0.50,
            'loja_id' => 1,
            'categoria' => 'Padaria',
            'ingredientes' => 'Farinha, água, sal, fermento',
            'informacoes_nutricionais' => json_encode(['calorias' => 120, 'proteinas' => 3]),
            'porcao' => '1 unidade',
            'alergenicos' => 'Contém glúten'
        ]);

        Product::create([
            'nome' => 'Bolo de Chocolate',
            'descricao' => 'Bolo molhadinho com cobertura de brigadeiro.',
            'preco' => 15.00,
            'loja_id' => 2,
            'categoria' => 'Confeitaria',
            'ingredientes' => 'Ovos, farinha, chocolate, açúcar',
            'informacoes_nutricionais' => json_encode(['calorias' => 350, 'proteinas' => 5]),
            'porcao' => '1 fatia',
            'alergenicos' => 'Contém ovos, leite e glúten'
        ]);
    }
}
