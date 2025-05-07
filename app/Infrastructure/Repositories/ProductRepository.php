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
}
