<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function create(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct($request->validated());
            return response()->json(['message' => 'Produto criado com sucesso', 'id' => $product->id], 201);
        } catch (ValidationException $e) {
            // Resposta para validação (produto duplicado)
            return response()->json(['message' => $e->errors()['produto'][0]], 409);
        } catch (\Exception $e) {
            // Para outros erros, retorna erro genérico
            return response()->json(['message' => 'Erro ao criar produto', 'error' => $e->getMessage()], 500);
        }
    }
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        return response()->json($product);
    }

    public function listByCategory(Request $request)
    {
        $categoria = $request->query('categoria');
        $produtos = $this->productService->listByCategory($categoria);

        return response()->json($produtos);
    }

    public function update(UpdateProductRequest $request): JsonResponse
    {
        $updated = $this->productService->update($request->validated());

        if (!$updated) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        return response()->json(['message' => 'Produto atualizado com sucesso']);
    }
    
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:products,id'
        ]);

        $deleted = $this->productService->delete($request->input('id'));

        if (!$deleted) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        return response()->json(['message' => 'Produto deletado com sucesso']);
    }
}
