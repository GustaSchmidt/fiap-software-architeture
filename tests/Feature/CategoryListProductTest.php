<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Loja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CategoryListProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar uma loja para associar o produto
        $loja = Loja::create([
            'nome' => 'Loja Teste',
            'endereco' => 'Rua Teste, 123',            
        ]);


        $this->loja = $loja;
    }

    #[Test]
    public function test_deve_retornar_produtos_filtrados_por_categoria()
    {
        // Arrange
        $loja = Loja::create([
            'nome' => 'Loja Teste',
            'endereco' => 'Rua Teste, 123',
            'telefone' => '11999999999',
        ]);

        $produtoBebida = [
            'nome' => 'Suco Natural',
            'preco' => 6.5,
            'categoria' => 'Bebidas',
            'ingredientes' => ['laranja'],
            'porcao' => '300ml',
            'informacoes_nutricionais' => [
                'calorias' => 100,
                'proteinas' => 0.5,
                'gorduras' => 0.1,
                'carboidratos' => 25.0,
            ],
            'alergenicos' => 'Não contém',
            'loja_id' => $loja->id,
        ];

        $produtoConfeitaria = [
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
            'loja_id' => $loja->id,
        ];

        // Criação dos produtos via API
        $this->postJson('/api/product/create', $produtoBebida)->assertStatus(201);
        $this->postJson('/api/product/create', $produtoConfeitaria)->assertStatus(201);

        // Act
        $response = $this->postJson('/api/product/category_list?categoria=Bebidas');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'nome' => 'Suco Natural',
            'categoria' => 'Bebidas',
            'porcao' => '300ml'
        ]);
        $response->assertJsonMissing([
            'nome' => 'Bolo de Cenoura'
        ]);
    }


}
