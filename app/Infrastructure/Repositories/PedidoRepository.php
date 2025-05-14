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
}
