<?php
namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Models\Sacola;
use App\Models\Product;
use Illuminate\Support\Facades\Log;


class SacolaRepository implements SacolaRepositoryInterface
{
    public function adicionarItem(int $clienteId, int $produtoId, int $quantidade): void
    {
        // Verificar se o cliente já tem uma sacola existente
        $sacola = Sacola::where('client_id', $clienteId)->first();

        // Se não existir uma sacola para o cliente, cria uma nova
        if (!$sacola) {
            $sacola = Sacola::create([
                'client_id' => $clienteId,
                'status' => 'aberta',
                'total' => 0,
            ]);
        }

        // Encontrar o produto
        $produto = Product::findOrFail($produtoId);

        // Verificar se o produto já está na sacola
        $produtoSacola = $sacola->products()->where('product_id', $produtoId)->first();

        if ($produtoSacola) {
            // Se o produto já estiver na sacola, atualizar a quantidade
            $produtoSacola->pivot->quantidade += $quantidade;
            $produtoSacola->pivot->save();
        } else {
            // Caso o produto não esteja na sacola, adicionar o produto
            $sacola->products()->attach($produto->id, ['quantidade' => $quantidade]);
        }

        // Atualizar o total da sacola
        $sacola->total += $produto->preco * $quantidade;
        $sacola->save();
    }


    public function listarPorCliente(int $clientId): array
    {
        // Buscar todas as sacolas do cliente
        $sacolas = Sacola::where('client_id', $clientId)->get();

        return $sacolas->map(function ($sacola) {
            return [
                'id' => $sacola->id,
                'client_id' => $sacola->client_id,
                'produtos' => $sacola->products->map(function ($produto) {
                    return [
                        'id' => $produto->id,
                        'nome' => $produto->nome,
                        'quantidade' => $produto->pivot->quantidade,
                        'preco' => $produto->preco,
                    ];
                }),
                'total' => $sacola->total,
            ];
        })->toArray();
    }
}
