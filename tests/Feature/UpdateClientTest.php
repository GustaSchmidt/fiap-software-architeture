<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Client;
use PHPUnit\Framework\Attributes\Test;

class UpdateClientTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function deve_atualizar_cliente_com_dados_validos()
    {
        $client = Client::create([
            'nome' => 'Gustavo',
            'sobrenome' => 'Schmidt',
            'email' => 'gustavo.schmidt@example.com',
            'cpf' => '688.537.450-45',
            'senha' => bcrypt('senha123'),
        ]);

        $response = $this->postJson('/api/client/update', [
            'id' => $client->id,
            'nome' => 'Maria Atualizada',
            'sobrenome' => 'Oliveira',
            'email' => 'maria.atualizada@example.com',
            'cpf' => '123.456.789-10',
            'senha' => md5('novaSenha123'), // conforme doc, é string (md5)
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Cliente atualizado com sucesso',
                     'client' => [
                         'id' => $client->id,
                         'nome' => 'Maria Atualizada',
                         'sobrenome' => 'Oliveira',
                         'email' => 'maria.atualizada@example.com',
                         'cpf' => '123.456.789-10',
                     ],
                 ]);
    }

}
