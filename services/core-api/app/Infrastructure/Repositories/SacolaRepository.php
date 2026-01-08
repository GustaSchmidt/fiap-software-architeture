<?php
namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Models\Sacola as Sacola;
use App\Domain\Entities\Sacola as DomainSacola;
use App\Models\Product;
use Exception;

class SacolaRepository implements SacolaRepositoryInterface
{
    /**
     * Adiciona um item a uma sacola de um cliente.
     *
     * @param int $clienteId
     * @param int $produtoId
     * @param int $quantidade
     * @return void
     */
    public function adicionarItem(int $clienteId, int $produtoId, int $quantidade): void
    {
        $sacola = Sacola::firstOrCreate(['client_id' => $clienteId, 'status' => 'aberta']);
        $produto = Product::findOrFail($produtoId);

        $sacola->produtos()->attach($produtoId, [
            'quantidade' => $quantidade,
            'preco_unitario' => $produto->preco
        ]);
        $thisola->total += ($produto->preco * $quantidade);
        $sacola->save();
    }

    /**
     * Lista os itens da sacola de um cliente.
     *
     * @param int $clientId
     * @return array
     */
    public function listarPorCliente(int $clientId): array
    {
        $sacola = Sacola::where('client_id', $clientId)->where('status', 'aberta')->first();

        if ($sacola) {
            $items = $sacola->produtos->map(function ($produto) {
                return [
                    'id' => $produto->id,
                    'nome' => $produto->nome,
                    'preco_unitario' => $produto->pivot->preco_unitario,
                    'quantidade' => $produto->pivot->quantidade
                ];
            });

            return [
                'id' => $sacola->id,
                'total' => $sacola->total,
                'produtos' => $items->toArray()
            ];
        }

        return ['id' => null, 'total' => 0, 'produtos' => []];
    }

    /**
     * Remove um item da sacola de um cliente.
     *
     * @param int $clientId
     * @param int $produtoId
     * @return void
     */
    public function removerItem(int $clientId, int $produtoId): void
    {
        $sacola = Sacola::where('client_id', $clientId)->where('status', 'aberta')->first();

        if ($sacola) {
            $produto = $sacola->produtos()->where('product_id', $produtoId)->first();
            if ($produto) {
                $sacola->total -= ($produto->pivot->preco_unitario * $produto->pivot->quantidade);
                $sacola->save();
                $sacola->produtos()->detach($produtoId);
            }
        }
    }

    /**
     * Encontra uma sacola pelo ID.
     *
     * @param int $sacolaId
     * @return DomainSacola
     */
    public function findById(int $sacolaId): DomainSacola
    {
        $sacola = Sacola::findOrFail($sacolaId);
        $produtos = $sacola->produtos->map(function ($produto) {
            return [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'preco_unitario' => $produto->pivot->preco_unitario,
                'quantidade' => $produto->pivot->quantidade
            ];
        })->toArray();
        return new DomainSacola(
            $sacola->id,
            $sacola->client_id,
            $sacola->status,
            $sacola->total,
            $produtos
        );
    }
    
    /**
     * Fecha a sacola do cliente.
     *
     * @param int $sacolaId
     * @param string $finalStatus
     * @return void
     */
    public function fecharSacola(int $sacolaId, string $finalStatus = 'concluida'): void
    {
        $sacola = Sacola::findOrFail($sacolaId);
        $sacola->status = $finalStatus;
        $sacola->save();
    }
    
    /**
     * Atualiza o status da sacola.
     *
     * @param int $sacolaId
     * @param string $status
     * @return void
     */
    public function updateStatus(int $sacolaId, string $status): void
    {
        $sacola = Sacola::findOrFail($sacolaId);
        $sacola->status = $status;
        $sacola->save();
    }
}