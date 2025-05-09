<?php
namespace App\Services;

use App\Models\Product;
use Illuminate\Validation\ValidationException;
use App\Domain\Repositories\ProductRepositoryInterface;

class ProductService
{
    private $productRepository;
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    public function createProduct(array $data): Product
    {
        // Verifica se o produto já existe para a loja
        $existingProduct = Product::where('nome', $data['nome'])
                                  ->where('loja_id', $data['loja_id'])
                                  ->first();

        if ($existingProduct) {
            // Lança uma exceção de validação com uma mensagem de erro
            throw ValidationException::withMessages([
                'produto' => 'Produto já existe para essa loja.',
            ]);
        }

        // Cria o novo produto
        return Product::create([
            'nome' => $data['nome'],
            'preco' => $data['preco'],
            'categoria' => $data['categoria'],
            'ingredientes' => json_encode($data['ingredientes']),
            'porcao' => $data['porcao'],
            'informacoes_nutricionais' => json_encode($data['informacoes_nutricionais']),
            'alergenicos' => $data['alergenicos'],
            'loja_id' => $data['loja_id'],
        ]);
    }
    public function getProductById(int $id): array
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new \Exception('Produto não encontrado');
        }

        return $product->toArray();
    }
    public function listByCategory(?string $categoria): array
    {
        return $this->productRepository->findByCategory($categoria);
    }
    public function update(array $data): bool
    {
        return $this->productRepository->update($data['id'], $data);
    }
}
