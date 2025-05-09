<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Product as DomainProduct;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Models\Product;

class ProductRepository implements ProductRepositoryInterface
{
    public function save(DomainProduct $domainProduct): DomainProduct
    {
        $model = Product::create([
            'nome' => $domainProduct->nome,
            'preco' => $domainProduct->preco,
            'categoria' => $domainProduct->categoria,
            'ingredientes' => json_encode($domainProduct->ingredientes),
            'porcao' => $domainProduct->porcao,
            'informacoes_nutricionais' => json_encode($domainProduct->informacoes_nutricionais),
            'alergenicos' => $domainProduct->alergenicos,
            'loja_id' => $domainProduct->loja_id,
        ]);

        $domainProduct->id = $model->id;
        return $domainProduct;
    }
    public function findById(int $id): ?DomainProduct
    {
        $product = Product::find($id); // Usando find() para buscar o produto
        if ($product) {
            return new DomainProduct(
                $product->id,
                $product->nome,
                $product->preco,
                $product->categoria,
                json_decode($product->ingredientes, true),
                $product->porcao,
                json_decode($product->informacoes_nutricionais, true),
                $product->alergenicos,
                $product->loja_id
            );
        }

        return null; // Retorna null se não encontrar o produto
    }

    public function findByCategory(?string $categoria): array
    {
        $query = Product::query();

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        return $query->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'nome' => $product->nome,
                'preco' => $product->preco,
                'categoria' => $product->categoria,
                'porcao' => $product->porcao,
            ];
        })->toArray();
    }

}
