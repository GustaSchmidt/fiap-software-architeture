<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Loja; // ajuste o namespace se necessário
use PHPUnit\Framework\Attributes\Test;

class CreateLojaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function deve_criar_loja_com_dados_validos()
    {
        $payload = [
            'nome' => 'Loja Centro',
            'endereco' => 'Rua das Flores, 123',
        ];
        $headers = $this->getApiAuthHeaders();
        $response = $this->withHeaders($headers)
                         ->postJson('/api/loja/create', $payload);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'nome',
                        'endereco',
                        'created_at',
                        'updated_at',
                    ],
                ])
                ->assertJson([
                    'message' => 'Loja criada com sucesso!',
                    'data' => [
                        'nome' => 'Loja Centro',
                        'endereco' => 'Rua das Flores, 123',
                    ],
                ]);
    }

}
