<?php

namespace App\Services;

use App\Domain\Entities\Sacola;
use App\Domain\Repositories\SacolaRepositoryInterface;

class SacolaService
{
    public function __construct(private SacolaRepositoryInterface $sacolaRepository) {}

    public function adicionarItem(int $clienteId, int $produtoId, int $quantidade): void
    {
        $this->sacolaRepository->adicionarItem($clienteId, $produtoId, $quantidade);
    }

    public function removerItem(int $clientId, int $produtoId): void
    {
        $this->sacolaRepository->removerItem($clientId, $produtoId);
    }

    public function checkout(int $clientId): array
    {
        return $this->sacolaRepository->checkout($clientId);
    }

    public function findById(int $sacolaId): Sacola
    {
        return $this->sacolaRepository->findById($sacolaId);
    }

    public function fecharSacola(int $sacolaId): void
    {
        $this->sacolaRepository->fecharSacola($sacolaId, 'concluida');
    }
}
