<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Domain\Repositories\PedidoRepositoryInterface;
use App\Services\SacolaService; // Ou SacolaRepositoryInterface se preferir injetar o repo diretamente
use App\Adapters\Gateways\MercadoPagoClient; // Cliente para consultar o pagamento
use App\Models\Pedido; // Model Eloquent
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class MercadoPagoWebhookController extends Controller
{
    protected PedidoRepositoryInterface $pedidoRepository;
    protected SacolaService $sacolaService;
    protected MercadoPagoClient $mercadoPagoClient;

    public function __construct(
        PedidoRepositoryInterface $pedidoRepository,
        SacolaService $sacolaService,
        MercadoPagoClient $mercadoPagoClient // Injete o cliente MP
    ) {
        $this->pedidoRepository = $pedidoRepository;
        $this->sacolaService = $sacolaService;
        $this->mercadoPagoClient = $mercadoPagoClient;
    }

    public function handleNotification(Request $request)
    {
        Log::info('Webhook do Mercado Pago Recebido:', $request->all());

        $type = $request->input('type');
        $paymentId = $request->input('data.id'); // ID do pagamento no Mercado Pago

        if ($type === 'payment' && $paymentId) {
            try {
                $pedidoModel = Pedido::where('mercado_pago_id', $paymentId)->first();

                if (!$pedidoModel) {
                    Log::warning("Webhook MercadoPago: Pedido não encontrado com mercado_pago_id: {$paymentId}");
                    return response()->json(['status' => 'pedido nao encontrado'], 200);
                }

                // Buscar os detalhes do pagamento no Mercado Pago para obter o status mais recente
                $paymentDetails = $this->mercadoPagoClient->getPaymentDetails($paymentId);
                $mpStatus = $paymentDetails['status']; // Ex: 'approved', 'cancelled', 'pending'

                // Processar apenas se o pedido ainda estiver aguardando pagamento ou em pagamento
                if (in_array($pedidoModel->status, ['aguardando_pagamento', 'em_pagamento'])) {
                    $newPedidoStatus = null;
                    $closeSacola = false;

                    switch ($mpStatus) {
                        case 'approved':
                            $newPedidoStatus = 'pago'; // Status do seu sistema para aprovado
                            $closeSacola = true;
                            Log::info("Webhook MP: Pagamento {$paymentId} para Pedido {$pedidoModel->id} APROVADO.");
                            break;
                        case 'cancelled':
                        case 'rejected':
                        case 'expired': // PIX expirado
                            $newPedidoStatus = 'cancelado'; // Status do seu sistema
                            Log::info("Webhook MP: Pagamento {$paymentId} para Pedido {$pedidoModel->id} FALHOU/CANCELADO (Status MP: {$mpStatus}).");
                            break;
                        case 'pending':
                        case 'in_process':
                            Log::info("Webhook MP: Pagamento {$paymentId} para Pedido {$pedidoModel->id} ainda pendente/em processo (Status MP: {$mpStatus}).");
                            break;
                        default:
                            Log::warning("Webhook MP: Status de pagamento não tratado '{$mpStatus}' para MP ID {$paymentId}.");
                            break;
                    }

                    if ($newPedidoStatus) {
                        $this->pedidoRepository->updateStatus($pedidoModel->id, $newPedidoStatus);
                        if ($closeSacola) {
                            // O método fecharSacola deve atualizar o status da sacola para algo como 'concluida' ou 'paga'
                            $this->sacolaService->fecharSacola($pedidoModel->sacola_id);
                        }
                    }
                } else {
                    Log::info("Webhook MP: Pedido ID {$pedidoModel->id} (status: {$pedidoModel->status}) não requer atualização por este webhook (MP Status: {$mpStatus}).");
                }

            } catch (Exception $e) {
                Log::error("Erro ao processar webhook do Mercado Pago: " . $e->getMessage(), ['payment_id' => $paymentId, 'exception' => $e]);
                return response()->json(['status' => 'erro', 'message' => $e->getMessage()], 500);
            }
        }
        return response()->json(['status' => 'recebido'], 200);
    }
}