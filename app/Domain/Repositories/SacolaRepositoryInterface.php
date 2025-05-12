<?php

namespace App\Domain\Repositories;

interface SacolaRepositoryInterface
{
    public function addItem(int $clienteId, int $produtoId, int $quantidade): int;
}
