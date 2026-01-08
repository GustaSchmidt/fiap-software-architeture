<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Sacola;

interface SacolaRepositoryInterface
{
    public function adicionarItem(int $clienteId, int $produtoId, int $quantidade): void;
    public function listarPorCliente(int $clientId): array;
    public function removerItem(int $clientId, int $produtoId): void;
    public function findById(int $sacolaId): Sacola;
}
