<?php

namespace App\Domain\Repositories;

interface SacolaRepositoryInterface
{
    public function adicionarItem(int $clienteId, int $produtoId, int $quantidade): void;
}
