<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CreateClientTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function deve_criar_cliente_com_dados_validos()
    {
        $headers = $this->getApiAuthHeaders();
        $response = $this->withHeaders($headers)
                         ->postJson('/api/client/create', [
            'nome' => 'João',
            'sobrenome' => 'Silva',
            'email' => 'joao.silva@example.com',
            'cpf' => '404.562.410-43',
            'senha' => md5('senha123'),
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id',
                     'message',
                 ])
                 ->assertJson([
                     'message' => 'Cliente criado com sucesso',
                 ]);
    }

}
