<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CheckoutService;
use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Domain\Repositories\PedidoRepositoryInterface;
use App\Domain\Entities\Pedido;
use Illuminate\Support\Facades\Http;
use Mockery;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase; // Importante para limpar o banco

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase; // Reseta o banco a cada teste (necessário para Client::factory)

    public function test_deve_criar_pedido_com_sucesso_ao_receber_ok_do_microsservico()
    {
        // 1. Preparar Mocks
        $sacolaRepoMock = Mockery::mock(SacolaRepositoryInterface::class);
        $pedidoRepoMock = Mockery::mock(PedidoRepositoryInterface::class);

        // Mock da Sacola
        $sacolaMock = (object) [
            'id' => 1,
            'produtos' => ['item1'],
            'total' => 100.00
        ];
        
        // Cria cliente real no banco em memória (SQLite)
        $cliente = Client::factory()->create(['id' => 1]);

        // Configurar expectativas
        $sacolaRepoMock->shouldReceive('findById')->with(1)->andReturn($sacolaMock);
        $sacolaRepoMock->shouldReceive('updateStatus')->with(1, 'em_pagamento');

        // Mock do Pedido Criado
        $pedidoEsperado = new Pedido(
            id: 123,
            client_id: 1,
            sacola_id: 1,
            status: 'aguardando_pagamento',
            total: 100.00,
            mercado_pago_id: 999999
        );
        $pedidoRepoMock->shouldReceive('criar')->andReturn($pedidoEsperado);

        // 2. Mockar HTTP (Sucesso)
        Http::fake([
            config('services.payment.url') . '/api/pay' => Http::response([
                'payment_id' => 999999,
                'qr_code' => '000201...',
                'qr_code_base64' => 'base64...',
                'status' => 'pending'
            ], 200),
        ]);

        // 3. Executar
        $service = new CheckoutService($sacolaRepoMock, $pedidoRepoMock);
        $resultado = $service->processarCheckout(1);

        // 4. Asserções
        $this->assertEquals(123, $resultado['pedido_id']);
    }

    public function test_deve_lancar_excecao_se_microsservico_falhar()
    {
        // 1. Preparar Mocks (O erro estava aqui: faltava configurar o mock igual acima)
        $sacolaRepoMock = Mockery::mock(SacolaRepositoryInterface::class);
        $pedidoRepoMock = Mockery::mock(PedidoRepositoryInterface::class);

        // Precisamos simular a sacola pois o service a busca ANTES de chamar o pagamento
        $sacolaMock = (object) [
            'id' => 1,
            'produtos' => ['item1'],
            'total' => 100.00
        ];

        // Cria cliente real
        Client::factory()->create(['id' => 1]);

        $sacolaRepoMock->shouldReceive('findById')->with(1)->andReturn($sacolaMock);
        
        // 2. Mockar HTTP (Erro 500)
        Http::fake([
            '*' => Http::response(['error' => 'Internal Server Error'], 500),
        ]);

        // 3. Asserção de Exceção
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro no serviço de pagamento');

        // 4. Executar
        $service = new CheckoutService($sacolaRepoMock, $pedidoRepoMock);
        $service->processarCheckout(1);
    }
}