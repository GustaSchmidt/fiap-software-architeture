<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CheckoutService;
use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Domain\Repositories\PedidoRepositoryInterface;
use App\Domain\Entities\Pedido;
use Illuminate\Support\Facades\Http;
use Mockery;
use App\Models\Client; // Se precisar mockar o Model Eloquent

class CheckoutServiceTest extends TestCase
{
    public function test_deve_criar_pedido_com_sucesso_ao_receber_ok_do_microsservico()
    {
        // 1. Preparar Mocks dos Repositórios
        $sacolaRepoMock = Mockery::mock(SacolaRepositoryInterface::class);
        $pedidoRepoMock = Mockery::mock(PedidoRepositoryInterface::class);

        // Mock da Sacola (objeto genérico ou Entity)
        $sacolaMock = (object) [
            'id' => 1,
            'produtos' => ['item1'], // Simulando não vazia
            'total' => 100.00
        ];
        
        // Mock do Cliente (Eloquent Model)
        // Nota: Como usamos Client::findOrFail no service, talvez precise de Factory ou mockar o Eloquent se for teste unitário puro.
        // Para simplificar, assumimos que é um Feature Test ou que o banco está em memória (sqlite).
        $cliente = Client::factory()->create(['id' => 1, 'cpf' => '12345678900']);

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

        // 2. IMPORTANTE: Mockar a chamada HTTP para o microsserviço Go
        Http::fake([
            // Intercepta qualquer chamada para o serviço de pagamento
            env('PAYMENT_SERVICE_URL') . '/api/pay' => Http::response([
                'payment_id' => 999999,
                'qr_code' => '00020126580014BR.GOV.BCB.PIX...',
                'qr_code_base64' => 'base64image...',
                'status' => 'pending'
            ], 200),
        ]);

        // 3. Executar o Service
        $service = new CheckoutService($sacolaRepoMock, $pedidoRepoMock);
        $resultado = $service->processarCheckout(1);

        // 4. Asserções
        $this->assertEquals(123, $resultado['pedido_id']);
        $this->assertEquals('00020126580014BR.GOV.BCB.PIX...', $resultado['pix_copia_cola']);
        
        // Verifica se a URL correta foi chamada
        Http::assertSent(function ($request) {
            return $request->url() == env('PAYMENT_SERVICE_URL') . '/api/pay' &&
                   $request['amount'] == 100.00 &&
                   $request['email'] == $cliente->email;
        });
    }

    public function test_deve_lancar_excecao_se_microsservico_falhar()
    {
        // ... Configurar mocks de repositório similar ao anterior ...
        $sacolaRepoMock = Mockery::mock(SacolaRepositoryInterface::class);
        $pedidoRepoMock = Mockery::mock(PedidoRepositoryInterface::class);
        // (Configure os returns do findById aqui...)

        // Simular Erro 500 do Go
        Http::fake([
            '*' => Http::response(['error' => 'Internal Server Error'], 500),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro no serviço de pagamento');

        $service = new CheckoutService($sacolaRepoMock, $pedidoRepoMock);
        $service->processarCheckout(1);
    }
}