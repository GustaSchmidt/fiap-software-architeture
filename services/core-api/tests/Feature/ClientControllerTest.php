<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Client;
use PHPUnit\Framework\Attributes\Test;


class SearchClientControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function deve_retornar_cliente_quando_id_existir()
    {
        $client = Client::create([
            'nome' => 'Gustavo',
            'sobrenome' => 'Schmidt',
            'email' => 'gustavo.schmidt@example.com',
            'cpf' => '688.537.450-45',
            'senha' => bcrypt('senha123'),
        ]);
        
        $headers = $this->getApiAuthHeaders();

        $response = $this->withHeaders($headers)
                         ->getJson("/api/client/{$client->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $client->id,
                     'nome' => 'Gustavo',
                     'sobrenome' => 'Schmidt',
                     'email' => 'gustavo.schmidt@example.com',
                     'cpf' => '688.537.450-45',
                 ]);
    }

    #[Test]
    public function deve_retornar_404_quando_cliente_nao_existir()
    {
        $headers = $this->getApiAuthHeaders();

        $response = $this->withHeaders($headers)->getJson('/api/client/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'message' => 'Cliente não encontrado',
                 ]);
    }
}
