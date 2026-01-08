<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Loja;

class SearchLojaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function deve_buscar_lojas_por_nome()
    {
        // Criar algumas lojas no banco para teste
        Loja::create(['nome' => 'Loja Centro', 'endereco' => 'Rua das Flores, 123']);
        Loja::create(['nome' => 'Loja Sul', 'endereco' => 'Avenida Brasil, 456']);
        Loja::create(['nome' => 'Centro Comercial', 'endereco' => 'Praça Central, 789']);
        $headers = $this->getApiAuthHeaders();
        
        $response = $this->withHeaders($headers)
                         ->postJson('/api/loja/search', [
            'nome' => 'Centro',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'nome', 'endereco']
                 ])
                 ->assertJsonFragment([
                     'nome' => 'Loja Centro',
                     'endereco' => 'Rua das Flores, 123',
                 ])
                 ->assertJsonFragment([
                     'nome' => 'Centro Comercial',
                     'endereco' => 'Praça Central, 789',
                 ])
                 ->assertJsonMissing([
                     'nome' => 'Loja Sul',
                 ]);
    }
}
