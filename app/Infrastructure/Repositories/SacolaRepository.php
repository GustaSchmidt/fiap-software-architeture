<?php
namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Models\Sacola as Sacola;
use App\Domain\Entities\Sacola as DomainSacola;
use App\Models\Product;
use App\Models\Pedido;
use App\Models\Client as Client; // Importar o modelo Client
use Illuminate\Support\Facades\Log;
use App\Adapters\Gateways\MercadoPagoClient;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception; // Para capturar exceções

class SacolaRepository implements SacolaRepositoryInterface
{
    protected MercadoPagoClient $mercadoPagoClient;

    // Injeção de Dependência do MercadoPagoClient
    public function __construct(MercadoPagoClient $mercadoPagoClient)
    {
        $this->mercadoPagoClient = $mercadoPagoClient;
    }

    public function adicionarItem(int $clienteId, int $produtoId, int $quantidade): void
    {
        $sacola = Sacola::where('client_id', $clienteId)
                                ->where('status', 'aberta') // Garante que só adiciona a sacolas abertas
                                ->first();

        if (!$sacola) {
            $sacola = Sacola::create([
                'client_id' => $clienteId,
                'status' => 'aberta',
                'total' => 0,
            ]);
        }

        $produto = Product::findOrFail($produtoId);
        $produtoNaSacola = $sacola->products()->where('product_id', $produtoId)->first();

        if ($produtoNaSacola) {
            $produtoNaSacola->pivot->quantidade += $quantidade;
            // Validação de quantidade máxima ou estoque pode ser adicionada aqui
            $produtoNaSacola->pivot->save();
        } else {
            $sacola->products()->attach($produtoId, ['quantidade' => $quantidade]);
        }
        $this->recalcularTotalSacola($sacola);
    }

    protected function recalcularTotalSacola(Sacola $sacola): void
    {
        $total = 0;
        // Eager load products se não vierem carregados para evitar N+1
        $sacola->loadMissing('products');
        foreach ($sacola->products as $produto) {
            $total += $produto->preco * $produto->pivot->quantidade;
        }
        $sacola->total = $total;
        $sacola->save();
    }

    public function listarPorCliente(int $clientId): array
    {
        $sacola = Sacola::where('client_id', $clientId)
            ->whereNotIn('status', ['fechada', 'pago_aprovado', 'cancelada']) // Exclui sacolas já finalizadas/canceladas
            ->with('products') // Eager load products
            ->first();

        if (!$sacola) {
            return [
                'client_id' => $clientId,
                'produtos' => [],
                'valor_total' => '0,00', // Manter formato
                'status_sacola' => 'inexistente'
            ];
        }

        $this->recalcularTotalSacola($sacola);

        return [
            'client_id' => $sacola->client_id,
            'sacola_id' => $sacola->id,
            'status_sacola' => $sacola->status,
            'produtos' => $sacola->products->map(function ($produto) {
                return [
                    'id_produto' => $produto->id,
                    'nome' => $produto->nome,
                    'quantidade' => $produto->pivot->quantidade,
                    'preco_unitario' => number_format($produto->preco, 2, ',', '.'),
                    'preco_total_item' => number_format($produto->preco * $produto->pivot->quantidade, 2, ',', '.')
                ];
            })->toArray(),
            'valor_total' => number_format($sacola->total, 2, ',', '.')
        ];
    }

    public function removerItem(int $clientId, int $produtoId): void
    {
        $sacola = Sacola::where('client_id', $clientId)
                                ->where('status', 'aberta') // Só permite remover de sacolas abertas
                                ->firstOrFail();

        $produtoNaSacola = $sacola->products()->where('product_id', $produtoId)->first();

        if ($produtoNaSacola) {
            $sacola->products()->detach($produtoId);
            $this->recalcularTotalSacola($sacola);
        } else {
            throw new Exception("Produto ID {$produtoId} não encontrado na sacola aberta do cliente ID {$clientId}.");
        }
    }

    public function checkout(int $clientId): array
    {
        $sacola = Sacola::where('client_id', $clientId)
                                ->where('status', 'aberta')
                                ->with('products')
                                ->firstOrFail();

        if ($sacola->products->isEmpty()) {
            throw new Exception("A sacola está vazia. Adicione produtos antes de fazer o checkout.");
        }

        $this->recalcularTotalSacola($sacola); // Garante que o total está correto
        $valorTotalPagamento = $sacola->total;

        if ($valorTotalPagamento <= 0) {
             throw new Exception("O valor total da sacola deve ser maior que zero para o checkout.");
        }

        $cliente = Client::findOrFail($clientId); //

        $payerInfo = [
            'email' => $cliente->email,
            'first_name' => $cliente->nome,
            'last_name' => $cliente->sobrenome,
            'identification_type' => 'CPF', // Ou lógica para determinar se é CPF/CNPJ
            'identification_number' => preg_replace('/\D/', '', $cliente->cpf), // Remove não-dígitos
        ];

        $descricaoPagamento = "Pedido da Sacola #{$sacola->id} - Cliente: {$cliente->nome}";
        $externalReference = "SAC_{$sacola->id}_CLI_{$clientId}_" . time();
        // Certifique-se de que esta rota existe e está configurada para webhooks
        $notificationUrl = route('webhooks.mercadopago.notification'); // Crie uma rota nomeada para isso

        try {
            $dadosPagamentoMP = $this->mercadoPagoClient->criarPagamentoPix(
                $valorTotalPagamento,
                $descricaoPagamento,
                $payerInfo,
                $externalReference,
                $notificationUrl
            );
        } catch (Exception $e) {
            Log::error("Falha no checkout - Erro ao criar pagamento PIX MP para sacola ID {$sacola->id}: " . $e->getMessage(), [
                'sacola_id' => $sacola->id,
                'client_id' => $clientId,
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString() // Para debug, pode ser muito verboso para produção
            ]);
            throw new Exception("Não foi possível processar seu pagamento no momento. Tente novamente mais tarde. Detalhe: " . $e->getMessage());
        }
        //
        $pedido = Pedido::create([
            'client_id' => $clientId,
            'sacola_id' => $sacola->id,
            'status' => 'aguardando_pagamento', // Status inicial do pedido
            'total' => $valorTotalPagamento,
            'payment_method' => 'pix_mercadopago',
            'mercado_pago_id' => $dadosPagamentoMP['id'] ?? null, // Salva o ID do pagamento do MP
            'external_payment_reference' => $externalReference,
        ]);

        $sacola->status = 'em_pagamento'; // Muda o status da sacola
        $sacola->save();

        Log::info("Checkout iniciado para sacola ID {$sacola->id}. Pedido ID {$pedido->id} criado. MP Payment ID {$dadosPagamentoMP['id']}.");

        return [
            'pedido_id' => $pedido->id,
            'status_pedido' => $pedido->status,
            'valor_total' => number_format($pedido->total, 2, ',', '.'),
            'metodo_pagamento' => $pedido->payment_method,
            'mercado_pago_payment_id' => $dadosPagamentoMP['id'] ?? null,
            'mercado_pago_payment_status' => $dadosPagamentoMP['status'] ?? null,
            'pix_qr_code_base64' => $dadosPagamentoMP['qr_code_base64'] ?? null,
            'pix_copia_cola' => $dadosPagamentoMP['qr_code_text'] ?? null,
            'mensagem' => 'Pedido realizado com sucesso! Utilize o QR Code ou o código "Copia e Cola" para efetuar o pagamento PIX.'
        ];
    }

    public function findById(int $sacolaId): DomainSacola
    {
        $Sacola = Sacola::with('products')->findOrFail($sacolaId);

        // Mapeia os dados do model Eloquent para a entidade DomainSacola
        $produtosArray = $Sacola->products->map(function ($produto) {
            return [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'preco' => (float) $produto->preco,
                'quantidade' => (int) $produto->pivot->quantidade,
            ];
        })->toArray();

        return new DomainSacola(
            $Sacola->id,
            $Sacola->client_id,
            $Sacola->status,
            $produtosArray,
            (float) $Sacola->total
        );
    }

    public function fecharSacola(int $clienteId): void
    {
        // Este método provavelmente deveria ser chamado após a confirmação do pagamento
        // ou se o cliente explicitamente abandona/cancela a sacola em andamento.
        $sacola = Sacola::where('client_id', $clienteId)
                                ->whereIn('status', ['em_pagamento', 'aguardando_pagamento']) // Status que podem ser fechados após confirmação ou falha
                                ->first();

        if ($sacola) {
            // A lógica de fechar a sacola dependerá se o pagamento foi bem-sucedido ou não.
            // Se o pagamento foi confirmado (via webhook), o status do pedido muda e a sacola pode ser 'fechada_paga'.
            // Se o pagamento falhou ou expirou, o status do pedido pode ser 'cancelado' e a sacola pode ser reaberta ou 'fechada_abandonada'.
            // Por ora, vamos apenas logar, pois a lógica completa de fechamento depende do fluxo de webhook.
            Log::info("Tentativa de fechar sacola ID {$sacola->id} para cliente ID {$clienteId}. Ação real dependeria do status do pagamento.");
            // Exemplo: se o pagamento foi confirmado:
            // $sacola->status = 'concluida';
            // $sacola->save();
        } else {
            Log::warning("Nenhuma sacola 'em_pagamento' ou 'aguardando_pagamento' encontrada para o cliente ID: {$clienteId} para fechar.");
        }
    }
}