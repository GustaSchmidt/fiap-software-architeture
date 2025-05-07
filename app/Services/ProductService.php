<?php
namespace App\Services;

use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepositoryInterface;

class ProductService
{
    public function __construct(private ProductRepositoryInterface $repository) {}

    public function createProduct(array $data): Product
    {
        return $this->repository->save(new Product(
            id: null,
            nome: $data['nome'],
            preco: $data['preco'],
            categoria: $data['categoria'],
            ingredientes: $data['ingredientes'],
            porcao: $data['porcao'],
            informacoes_nutricionais: $data['informacoes_nutricionais'],
            alergenicos: $data['alergenicos'] ?? null,
            loja_id: $data['loja_id'],
        ));
    }
}
