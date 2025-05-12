<?php

namespace App\Services;

use App\Domain\Repositories\SacolaRepositoryInterface;

class SacolaService
{
    protected SacolaRepositoryInterface $repository;

    public function __construct(SacolaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function addItemToSacola(int $clienteId, int $produtoId, int $quantidade): int
    {
        // Lógica para adicionar o item à sacola
        return $this->repository->addItem($clienteId, $produtoId, $quantidade);
    }
}
