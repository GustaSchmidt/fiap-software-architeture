<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Pedido as DomainPedido;
use App\Domain\Repositories\PedidoRepositoryInterface;
use App\Models\Pedido;

class PedidoRepository implements PedidoRepositoryInterface
{
    public function criar(DomainPedido $pedido): DomainPedido
    {
        $model = Pedido::create([
            'client_id' => $pedido->client_id,
            'sacola_id' => $pedido->sacola_id,
            'status' => $pedido->status,
            'total' => $pedido->total,
            'mercado_pago_id' => $pedido->mercado_pago_id,
        ]);

        $pedido->id = $model->id;
        return $pedido;
    }

    public function buscarPorLojaEFiltro(int $lojaId, array $filtros): array
    {
        $query = Pedido::whereHas('sacola.products', function ($q) use ($lojaId) {
            $q->where('loja_id', $lojaId);
        });

        if (!empty($filtros['client_id'])) {
            $query->where('client_id', $filtros['client_id']);
        }

        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        return $query->with('sacola.products')->get()->map(function ($pedido) {
            return [
                'pedido_id' => $pedido->id,
                'cliente_id' => $pedido->client_id,
                'status' => $pedido->status,
                'total' => $pedido->total,
                'produtos' => $pedido->sacola->products->map(function ($produto) {
                    return [
                        'id' => $produto->id,
                        'nome' => $produto->nome,
                        'quantidade' => $produto->pivot->quantidade,
                        'preco' => $produto->preco,
                    ];
                })
            ];
        })->toArray();
    }
    
    public function findById(int $id): ?DomainPedido
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return null;
        }

        return new DomainPedido(
            $pedido->id,
            $pedido->client_id,
            $pedido->sacola_id,
            $pedido->status,
            $pedido->total,
            $pedido->mercado_pago_id
        );
    }
}
