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
            'ingredientes' => json_encode(['Farinha', 'Água', 'Sal', 'Fermento']),
            'informacoes_nutricionais' => json_encode(['calorias' => 120, 'proteinas' => 3]),
            'porcao' => '1 unidade',
            'alergenicos' => 'Contém glúten'
        ]);

        Product::create([
            'nome' => 'Croissant de Queijo',
            'descricao' => 'Croissant leve recheado com queijo.',
            'preco' => 6.50,
            'loja_id' => 1,
            'categoria' => 'Padaria',
            'ingredientes' => json_encode(['Farinha de trigo', 'Manteiga', 'Queijo', 'Fermento', 'Sal']),
            'informacoes_nutricionais' => json_encode(['calorias' => 300, 'proteinas' => 6]),
            'porcao' => '1 unidade',
            'alergenicos' => 'Contém leite e glúten'
        ]);

        Product::create([
            'nome' => 'Baguete Integral',
            'descricao' => 'Pão baguete com farinha integral.',
            'preco' => 4.00,
            'loja_id' => 1,
            'categoria' => 'Padaria',
            'ingredientes' => json_encode(['Farinha integral', 'Água', 'Sal', 'Fermento']),
            'informacoes_nutricionais' => json_encode(['calorias' => 180, 'proteinas' => 5]),
            'porcao' => '1 unidade',
            'alergenicos' => 'Contém glúten'
        ]);

        Product::create([
            'nome' => 'Bolo de Chocolate',
            'descricao' => 'Bolo molhadinho com cobertura de brigadeiro.',
            'preco' => 15.00,
            'loja_id' => 2,
            'categoria' => 'Confeitaria',
            'ingredientes' => json_encode(['Ovos', 'Farinha', 'Chocolate', 'Açúcar']),
            'informacoes_nutricionais' => json_encode(['calorias' => 350, 'proteinas' => 5]),
            'porcao' => '1 fatia',
            'alergenicos' => 'Contém ovos, leite e glúten'
        ]);

        Product::create([
            'nome' => 'Torta de Limão',
            'descricao' => 'Torta gelada com base crocante e recheio de limão.',
            'preco' => 18.00,
            'loja_id' => 2,
            'categoria' => 'Confeitaria',
            'ingredientes' => json_encode(['Leite condensado', 'Limão', 'Biscoito', 'Manteiga']),
            'informacoes_nutricionais' => json_encode(['calorias' => 400, 'proteinas' => 4]),
            'porcao' => '1 fatia',
            'alergenicos' => 'Contém leite, glúten'
        ]);

        Product::create([
            'nome' => 'Cookies de Aveia',
            'descricao' => 'Cookies crocantes com gotas de chocolate.',
            'preco' => 10.00,
            'loja_id' => 2,
            'categoria' => 'Confeitaria',
            'ingredientes' => json_encode(['Aveia', 'Açúcar mascavo', 'Chocolate', 'Ovos', 'Farinha']),
            'informacoes_nutricionais' => json_encode(['calorias' => 250, 'proteinas' => 4]),
            'porcao' => '2 unidades',
            'alergenicos' => 'Contém ovos, glúten'
        ]);

    }
}
