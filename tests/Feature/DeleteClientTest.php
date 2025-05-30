<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Client;
use PHPUnit\Framework\Attributes\Test;

class DeleteClientTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function deve_deletar_cliente_quando_id_existir()
    {
        $client = Client::create([
            'nome' => 'Maria',
            'sobrenome' => 'Oliveira',
            'email' => 'maria.oliveira@example.com',
            'cpf' => '123.456.789-00',
            'senha' => bcrypt('senha123'),
        ]);
        $headers = $this->getApiAuthHeaders();
        
        $response = $this->withHeaders($headers)
                         ->deleteJson("/api/client/delete?id={$client->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Cliente deletado com sucesso',
                 ]);

        $this->assertDatabaseMissing('clients', [
            'id' => $client->id,
        ]);
    }

    #[Test]
    public function deve_retornar_404_quando_cliente_nao_existir()
    {
        $headers = $this->getApiAuthHeaders();
        
        $response = $this->withHeaders($headers)
                         ->deleteJson('/api/client/delete?id=999999');

        $response->assertStatus(404)
                 ->assertJson([
                     'message' => 'Cliente não encontrado',
                 ]);
    }
}
