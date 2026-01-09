<?php

namespace App\Services;

use App\Domain\Entities\Pedido as DomainPedido;
use App\Domain\Repositories\PedidoRepositoryInterface;
use App\Domain\Repositories\SacolaRepositoryInterface;
// use App\Adapters\Gateways\MercadoPagoClient; // Removido: Agora é um microsserviço externo
use Illuminate\Support\Facades\Http;
use App\Models\Client;
use Exception;

class CheckoutService
{
    public function __construct(
        private SacolaRepositoryInterface $sacolaRepository,
        private PedidoRepositoryInterface $pedidoRepository
        // private MercadoPagoClient $mercadoPagoClient // Removido
    ) {}

    /**
     * Processa o checkout de uma sacola e solicita pagamento ao microsserviço.
     *
     * @param int $clientId
     * @return array
     * @throws Exception
     */
    public function processarCheckout(int $clientId): array
    {
        // 1. Busca a sacola e recalcula o total
        $sacola = $this->sacolaRepository->findById($clientId);

        if (empty($sacola->produtos)) {
            throw new Exception("A sacola está vazia.");
        }

        $valorTotalPagamento = $sacola->total;

        if ($valorTotalPagamento <= 0) {
             throw new Exception("O valor total da sacola deve ser maior que zero.");
        }

        // 2. Busca o cliente
        $cliente = Client::findOrFail($clientId);

        // 3. Comunicação com o Microsserviço de Pagamento (Go)
        // URL definida no .env, ex: http://payment-service:8081
        $paymentServiceUrl = config('services.payment.url') ?? env('PAYMENT_SERVICE_URL'); 

        if (!$paymentServiceUrl) {
            throw new Exception("A URL do serviço de pagamento não está configurada.");
        }

        try {
            // Monta o payload conforme esperado pelo serviço em Go (struct PaymentRequest)
            $payload = [
                'amount'      => (float) $valorTotalPagamento,
                'description' => "Pedido da Sacola #{$sacola->id}",
                'email'       => $cliente->email,
                'order_id'    => $sacola->id, // Usando ID da sacola como referência provisória
                'first_name'  => $cliente->nome,
                'last_name'   => $cliente->sobrenome,
                'cpf'         => $cliente->cpf
            ];
            $response = Http::timeout(10)->post("{$paymentServiceUrl}/api/pay", $payload);

            if ($response->failed()) {
                throw new Exception("Erro no serviço de pagamento: " . $response->body());
            }

            $dadosPagamentoMP = $response->json();

        } catch (Exception $e) {
            throw new Exception("Falha ao comunicar com o serviço de pagamento: " . $e->getMessage());
        }

        // 4. Cria o pedido no banco de dados local
        $novoPedido = new DomainPedido(
            id: null,
            client_id: $clientId,
            sacola_id: $sacola->id,
            status: 'aguardando_pagamento',
            total: $valorTotalPagamento,
            mercado_pago_id: $dadosPagamentoMP['payment_id'] ?? null 
        );
        
        $pedidoCriado = $this->pedidoRepository->criar($novoPedido);

        // 5. Atualiza o status da sacola
        $this->sacolaRepository->updateStatus($sacola->id, 'em_pagamento');

        // 6. Retorna os dados
        return [
            'pedido_id'          => $pedidoCriado->id,
            'status_pedido'      => $pedidoCriado->status,
            'valor_total'        => $pedidoCriado->total,
            'pix_qr_code_base64' => $dadosPagamentoMP['qr_code_base64'] ?? null, 
            'pix_copia_cola'     => $dadosPagamentoMP['qr_code'] ?? null,
            'mensagem'           => 'Pedido realizado com sucesso!',
        ];
    }
}