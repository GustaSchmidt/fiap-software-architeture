<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Pedido;

interface PedidoRepositoryInterface
{
    public function criar(Pedido $pedido): Pedido;
    public function buscarPorLojaEFiltro(int $lojaId, array $filtros): array;
    public function findById(int $id): ?Pedido;
}
