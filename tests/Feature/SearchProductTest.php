<?php

namespace Tests\Feature;

use App\Models\Loja;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SearchProductTest extends TestCase
{
    use RefreshDatabase;

    protected $loja;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loja = Loja::create([
            'nome' => 'Loja Teste',
            'endereco' => 'Rua Teste, 123'
        ]);

    }
    
    #[Test]
    public function deve_retornar_produto_existente()
    {
        $product = Product::create([
            'nome' => 'Bolo de Cenoura TESTE',
            'preco' => 1200000.5,
            'categoria' => 'Confeitaria',
            'ingredientes' => json_encode(['cenoura', 'açúcar', 'farinha de trigo', 'ovos']),
            'porcao' => '10000g',
            'informacoes_nutricionais' => json_encode(['calorias' => 120, 'proteinas' => 3]),
            'alergenicos' => 'Contém glúten e ovos, amendoas',
            'loja_id' => $this->loja->id,
        ]);


        $response = $this->getJson("/api/product/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $product->id,
            'nome' => 'Bolo de Cenoura TESTE',
            'preco' => 1200000.5,
            'categoria' => 'Confeitaria',
            'porcao' => '10000g',
            'alergenicos' => 'Contém glúten e ovos, amendoas',
            'loja_id' => $this->loja->id,
        ]);
    }


}
