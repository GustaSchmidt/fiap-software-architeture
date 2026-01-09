<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class MercadoPagoWebhookController extends Controller
{
    public function handleNotification(Request $request)
    {
        // 1. Log inicial para rastreabilidade
        Log::info('[Gateway] Webhook do Mercado Pago Recebido. Encaminhando para Payment Service.', [
            'id' => $request->input('data.id'),
            'type' => $request->input('type')
        ]);

        // 2. Obtém a URL do serviço interno de pagamentos
        // Certifique-se de que PAYMENT_SERVICE_URL está no seu .env (ex: http://payment-service:8081)
        $paymentServiceUrl = config('services.payment.url') ?? env('PAYMENT_SERVICE_URL');

        if (!$paymentServiceUrl) {
            Log::critical('[Gateway] URL do serviço de pagamento não configurada.');
            return response()->json(['error' => 'Configuration error'], 500);
        }

        try {
            // 3. Encaminha a requisição exatamente como chegou para o microsserviço
            // O serviço em Go será responsável por validar o ID no Mercado Pago e atualizar o banco
            $response = Http::timeout(10) // Timeout curto para não prender a conexão do Mercado Pago
                ->post("{$paymentServiceUrl}/api/webhook", $request->all());

            // 4. Verifica se o microsserviço recebeu com sucesso
            if ($response->successful()) {
                Log::info('[Gateway] Webhook encaminhado com sucesso para Payment Service.');
                return response()->json(['status' => 'forwarded'], 200);
            } else {
                Log::error('[Gateway] Payment Service retornou erro ao receber webhook.', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json(['status' => 'error_forwarding'], 500);
            }

        } catch (Exception $e) {
            Log::error('[Gateway] Falha de conexão ao encaminhar webhook.', [
                'message' => $e->getMessage()
            ]);

            // Falha de rede interna: Retornamos 500 para o Mercado Pago tentar novamente
            return response()->json(['status' => 'connection_error'], 500);
        }
    }
}