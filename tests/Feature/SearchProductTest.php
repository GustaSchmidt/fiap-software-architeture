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
            'nome' => 'Bolo de Cenoura',
            'preco' => 12.5,
            'categoria' => 'Confeitaria',
            'ingredientes' => ['cenoura', 'açúcar', 'farinha de trigo', 'ovos'],
            'porcao' => '100g',
            'informacoes_nutricionais' => [
                'calorias' => 250,
                'proteinas' => 3.5,
                'gorduras' => 8.0,
                'carboidratos' => 35.0,
            ],
            'alergenicos' => 'Contém glúten e ovos',
            'loja_id' => $this->loja->id,
        ]);

        $response = $this->getJson("/api/product/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $product->id,
            'nome' => 'Bolo de Cenoura',
            'preco' => 12.5,
            'categoria' => 'Confeitaria',
            'porcao' => '100g',
            'alergenicos' => 'Contém glúten e ovos',
            'loja_id' => $this->loja->id,
        ]);
    }


}
