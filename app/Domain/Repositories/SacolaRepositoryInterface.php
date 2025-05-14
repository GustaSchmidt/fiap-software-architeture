<?php

namespace App\Domain\Repositories;

interface SacolaRepositoryInterface
{
    public function adicionarItem(int $clienteId, int $produtoId, int $quantidade): void;
    public function listarPorCliente(int $clientId): array;
    public function removerItem(int $clientId, int $produtoId): void;
}
