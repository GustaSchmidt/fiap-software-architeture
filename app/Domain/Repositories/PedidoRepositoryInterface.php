<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Pedido;

interface PedidoRepositoryInterface
{
    public function criar(Pedido $pedido): Pedido;
}
