<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Models\Sacola;
use App\Models\Product;

class SacolaRepository implements SacolaRepositoryInterface
{
    public function adicionarItem(int $clienteId, int $produtoId, int $quantidade): void
    {
        $sacola = Sacola::findOrFail($clienteId);
        $produto = Product::findOrFail($produtoId);

        $sacola->products()->attach($produto->id, ['quantidade' => $quantidade]);
    }
}
