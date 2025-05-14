<?php
namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Models\Sacola;
use App\Models\Product;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use App\Adapters\Gateways\MercadoPagoClient;

class SacolaRepository implements SacolaRepositoryInterface
{
    public function adicionarItem(int $clienteId, int $produtoId, int $quantidade): void
    {
        // Verificar se o cliente já tem uma sacola existente
        $sacola = Sacola::where('client_id', $clienteId)->first();

        // Se não existir uma sacola para o cliente, cria uma nova
        if (!$sacola) {
            $sacola = Sacola::create([
                'client_id' => $clienteId,
                'status' => 'aberta',
                'total' => 0,
            ]);
        }

        // Encontrar o produto
        $produto = Product::findOrFail($produtoId);

        // Verificar se o produto já está na sacola
        $produtoSacola = $sacola->products()->where('product_id', $produtoId)->first();

        if ($produtoSacola) {
            // Se o produto já estiver na sacola, atualizar a quantidade
            $produtoSacola->pivot->quantidade += $quantidade;
            $produtoSacola->pivot->save();
        } else {
            // Caso o produto não esteja na sacola, adicionar o produto
            $sacola->products()->attach($produto->id, ['quantidade' => $quantidade]);
        }

        // Atualizar o total da sacola
        $sacola->total += $produto->preco * $quantidade;
        $sacola->save();
    }

    public function listarPorCliente(int $clientId): array
    {
        $sacola = Sacola::where('client_id', $clientId)
            ->where('status', '!=', 'em_pagamento')
            ->first();

        if (!$sacola) {
            return [
                'client_id' => $clientId,
                'produtos' => [],
                'valor_total' => 0
            ];
        }

        return [
            'client_id' => $sacola->client_id,
            'produtos' => $sacola->products->map(function ($produto) {
                return [
                    'id_produto' => $produto->id,
                    'nome' => $produto->nome,
                    'quantidade' => $produto->pivot->quantidade,
                    'preco' => number_format($produto->preco, 2)
                ];
            })->toArray(),
            'valor_total' => $sacola->total
        ];
    }
    public function removerItem(int $clientId, int $produtoId): void
    {
        $sacola = Sacola::where('client_id', $clientId)->firstOrFail();

        $produto = $sacola->products()->where('product_id', $produtoId)->first();

        if ($produto) {
            $totalRemovido = $produto->preco * $produto->pivot->quantidade;
            $sacola->products()->detach($produtoId);
            $sacola->total -= $totalRemovido;
            $sacola->save();
        }else{
            throw new \Exception("Produto não encontrado na sacola do cliente.");
        }
    }

    public function checkout(int $clientId): array
    {
        $sacola = Sacola::where('client_id', $clientId)->where('status', 'aberta')->firstOrFail();
        $mercadoPago = new MercadoPagoClient();

        $pagamento = $mercadoPago->criarPagamentoPix($sacola->total, "Pagamento Sacola #{$sacola->id}");

        $pedido = Pedido::create([
            'client_id' => $clientId,
            'sacola_id' => $sacola->id,
            'status' => 'aguardando_pagamento',
            'total' => $sacola->total,
            'mercado_pago_id' => $pagamento['id'],
        ]);

        $sacola->status = 'em_pagamento';
        $sacola->save();

        return [
            'pedido_id' => $pedido->id,
            'qr_code' => $pagamento['qr_code_base64'],
            'status' => $pedido->status,
            'valor' => $pedido->total,
        ];
    }
}
