<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Product as DomainProduct;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Exception;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Salva ou atualiza um produto no banco de dados.
     *
     * @param DomainProduct $domainProduct
     * @return DomainProduct
     * @throws Exception
     */
    public function save(DomainProduct $domainProduct): DomainProduct
    {
        $model = $domainProduct->id
            ? Product::find($domainProduct->id)
            : new Product();

        if ($model) {
            $model->nome = $domainProduct->nome;
            $model->preco = $domainProduct->preco;
            $model->categoria = $domainProduct->categoria;
            $model->ingredientes = json_encode($domainProduct->ingredientes);
            $model->porcao = $domainProduct->porcao;
            $model->informacoes_nutricionais = json_encode($domainProduct->informacoes_nutricionais);
            $model->alergenicos = $domainProduct->alergenicos;
            $model->loja_id = $domainProduct->loja_id;
            
            $model->save();
            $domainProduct->id = $model->id;
            return $domainProduct;
        }

        throw new Exception('Produto não encontrado para atualização.');
    }

    /**
     * Verifica se um produto com o mesmo nome já existe para uma loja específica.
     *
     * @param string $nome
     * @param int $lojaId
     * @return bool
     */
    public function existsForLoja(string $nome, int $lojaId): bool
    {
        return Product::where('nome', $nome)->where('loja_id', $lojaId)->exists();
    }

    /**
     * Encontra um produto pelo seu ID.
     *
     * @param int $id
     * @return DomainProduct|null
     */
    public function findById(int $id): ?DomainProduct
    {
        $product = Product::find($id);
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

        return null;
    }

    /**
     * Encontra produtos por categoria.
     *
     * @param string|null $categoria
     * @return array
     */
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
    
    /**
     * Atualiza um produto.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $product = Product::find($id);

        if (!$product) {
            return false;
        }

        $product->nome = $data['nome'];
        $product->preco = $data['preco'];
        $product->categoria = $data['categoria'];
        $product->ingredientes = json_encode($data['ingredientes']);
        $product->porcao = $data['porcao'];
        $product->informacoes_nutricionais = json_encode($data['informacoes_nutricionais']);
        $product->alergenicos = $data['alergenicos'] ?? null;
        $product->loja_id = $data['loja_id'];

        return $product->save();
    }

    /**
     * Deleta um produto.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $product = Product::find($id);

        if (!$product) {
            return false;
        }

        return $product->delete();
    }
}