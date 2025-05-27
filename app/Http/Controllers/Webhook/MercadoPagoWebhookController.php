<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function handleNotification(Request $request)
    {
        // Log para verificar se o webhook está sendo chamado
        Log::info('Webhook do Mercado Pago Recebido:', $request->all());

        $type = $request->input('type');
        $dataId = $request->input('data.id');

        if ($type === 'payment') { 
            // Processe a notificação:
            // 1. Verifique a autenticidade da notificação (se o Mercado Pago fornecer um mecanismo para isso).
            // 2. Busque o pedido no seu banco de dados usando o $dataId (que é o ID do pagamento no MP)
            //    ou uma referência externa que você enviou.
            // 3. Atualize o status do seu pedido (ex: para 'pago_aprovado', 'cancelado', etc.).
            // 4. Se o pagamento foi aprovado, você pode querer:
            //    - Liberar o produto/serviço.
            //    - Enviar um email de confirmação para o cliente.
            //    - Chamar o método $sacolaRepository->fecharSacola(...) com o status apropriado.

            Log::info("Notificação de pagamento do Mercado Pago processada: ID {$dataId}, Tipo: {$type}");
        }

        // Responda ao Mercado Pago com um status 200 OK para confirmar o recebimento.
        // Qualquer outra resposta pode fazer com que o Mercado Pago tente reenviar a notificação.
        return response()->json(['status' => 'recebido'], 200);
    }
}