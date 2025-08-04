<?php

namespace App\Services;

use App\Domain\Entities\Pedido as DomainPedido;
use App\Domain\Repositories\PedidoRepositoryInterface;
use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Adapters\Gateways\MercadoPagoClient;
use App\Models\Client;
use Exception;

class CheckoutService
{
    public function __construct(
        private SacolaRepositoryInterface $sacolaRepository,
        private PedidoRepositoryInterface $pedidoRepository,
        private MercadoPagoClient $mercadoPagoClient
    ) {}

    /**
     * Processa o checkout de uma sacola e cria um pedido de pagamento.
     *
     * @param int $clientId
     * @return array
     * @throws Exception
     */
    public function processarCheckout(int $clientId): array
    {
        // 1. Busca a sacola e recalcula o total (lógica de negócio)
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

        // 3. Interage com a API de pagamento (adaptador de infraestrutura)
        $payerInfo = [
            'email' => $cliente->email,
            'first_name' => $cliente->nome,
            'last_name' => $cliente->sobrenome,
            'identification_type' => 'CPF',
            'identification_number' => $cliente->cpf,
        ];
        $notificationUrl = route('webhooks.mercadopago.notification');
        $externalReference = "SAC_{$sacola->id}_CLI_{$clientId}_" . time();

        try {
            $dadosPagamentoMP = $this->mercadoPagoClient->criarPagamentoPix(
                $valorTotalPagamento,
                "Pedido da Sacola #{$sacola->id}",
                $payerInfo,
                $externalReference,
                $notificationUrl
            );
        } catch (Exception $e) {
            throw new Exception("Falha ao processar pagamento: " . $e->getMessage());
        }

        // 4. Cria o pedido no banco de dados (usando o PedidoRepository)
        $novoPedido = new DomainPedido(
            id: null,
            client_id: $clientId,
            sacola_id: $sacola->id,
            status: 'aguardando_pagamento',
            total: $valorTotalPagamento,
            mercado_pago_id: $dadosPagamentoMP['id']
        );
        $pedidoCriado = $this->pedidoRepository->criar($novoPedido);

        // 5. Atualiza o status da sacola (usando o SacolaRepository)
        $this->sacolaRepository->updateStatus($sacola->id, 'em_pagamento');

        // 6. Retorna os dados necessários para o cliente
        return [
            'pedido_id' => $pedidoCriado->id,
            'status_pedido' => $pedidoCriado->status,
            'valor_total' => $pedidoCriado->total,
            'pix_qr_code_base64' => $dadosPagamentoMP['qr_code_base64'],
            'pix_copia_cola' => $dadosPagamentoMP['qr_code_text'],
            'mensagem' => 'Pedido realizado com sucesso!',
        ];
    }
}