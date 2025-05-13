<?php

namespace App\Services;

use App\Domain\Repositories\SacolaRepositoryInterface;

class SacolaService
{
    public function __construct(private SacolaRepositoryInterface $sacolaRepository) {}

    public function adicionarItem(int $clienteId, int $produtoId, int $quantidade): void
    {
        $this->sacolaRepository->adicionarItem($clienteId, $produtoId, $quantidade);
    }
}
