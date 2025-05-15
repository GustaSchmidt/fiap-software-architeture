<?php

namespace App\Services;

use App\Domain\Repositories\PedidoRepositoryInterface;
use App\Services\SacolaService;
use Exception;

class PedidoService
{
    public function __construct(
        private PedidoRepositoryInterface $repository,
        private SacolaService $sacolaService
    ) {}

    public function listarPedidos(int $lojaId, array $filtros = []): array
    {
        return $this->repository->buscarPorLojaEFiltro($lojaId, $filtros);
    }
    
    public function getStatus(int $pedidoId): string
    {
        $pedido = $this->repository->findById($pedidoId);

        if (!$pedido) {
            throw new Exception("Pedido não encontrado.");
        }

        return $pedido->status;
    }
    
    public function updatePedido(int $pedidoId, string $newStatus)
    {
        $pedido = $this->repository->findById($pedidoId);

        if (!$pedido) {
            throw new Exception("Pedido não encontrado");
        }

        // Busca a sacola usando o serviço
        $sacola = $this->sacolaService->findById($pedido->sacola_id);

        if (!$sacola) {
            throw new Exception("Sacola associada ao pedido não encontrada");
        }

        if ($sacola->status !== 'pago') {
            throw new Exception("Status da sacola deve ser 'pago' para atualizar o pedido");
        }

        // Atualiza o status do pedido
        $pedidoAtualizado = $this->repository->updateStatus($pedidoId, $newStatus);

        // Fecha a sacola após atualização do pedido
        $this->sacolaService->fecharSacola($sacola->id);

        return $pedidoAtualizado;
    }
}
