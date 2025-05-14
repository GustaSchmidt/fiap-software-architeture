<?php

namespace App\Services;

use App\Domain\Repositories\PedidoRepositoryInterface;

class PedidoService
{
    public function __construct(private PedidoRepositoryInterface $repository) {}

    public function listarPedidos(int $lojaId, array $filtros = []): array
    {
        return $this->repository->buscarPorLojaEFiltro($lojaId, $filtros);
    }
}
