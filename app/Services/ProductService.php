<?php
namespace App\Services;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class ProductService
{
    /**
     * Cria um novo produto, com verificação de duplicidade.
     *
     * @param array $data
     * @return Product
     * @throws ValidationException
     */
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
}
