<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Client;

class SearchClientByCpfTest extends TestCase
{
    use RefreshDatabase;

    /** @test sucess */
    public function deve_retornar_cliente_quando_cpf_existir()
    {
        $client = Client::create([
            'nome' => 'Gustavo',
            'sobrenome' => 'Schmidt',
            'email' => 'gustavo.schmidt@example.com',
            'cpf' => '404.562.410-43',
            'senha' => bcrypt('senha123'),
        ]);

        $response = $this->postJson('/api/client/search_cpf', [
            'cpf' => '404.562.410-43',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $client->id,
                     'nome' => 'Gustavo',
                     'sobrenome' => 'Schmidt',
                     'email' => 'gustavo.schmidt@example.com',
                     'cpf' => '404.562.410-43',
                 ]);
    }

    /** @test error*/
    public function deve_retornar_404_quando_cpf_nao_existir()
    {
        $response = $this->postJson('/api/client/search_cpf', [
            'cpf' => '894.673.080-37', // CPF válido mas não existe
        ]);

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Cliente não encontrado',
                ]);
    }

}
