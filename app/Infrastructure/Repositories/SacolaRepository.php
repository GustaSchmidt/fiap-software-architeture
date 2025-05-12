<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Models\Sacola;
use App\Models\Produto;

class SacolaRepository implements SacolaRepositoryInterface
{
    public function addItem(int $clienteId, int $produtoId, int $quantidade): int
    {
        // Cria ou busca a sacola do cliente com status 'aberta'
        $sacola = Sacola::firstOrCreate(
            ['cliente_id' => $clienteId, 'status' => 'aberta'],
            ['total' => 0]
        );

        // Adiciona o produto à sacola (você precisa garantir o relacionamento entre a Sacola e Produto)
        $sacola->produtos()->attach($produtoId, ['quantidade' => $quantidade]);

        // Atualiza o total da sacola (isso é apenas um exemplo, você pode precisar ajustar a lógica)
        $sacola->total += $quantidade * $sacola->produtos()->find($produtoId)->preco;
        $sacola->save();

        return $sacola->id;
    }
}
