<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct(private ProductService $service) {}

    public function create(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->service->createProduct($request->validated());
            return response()->json(['message' => 'Produto criado com sucesso', 'id' => $product->id], 201);
        } catch (ValidationException $e) {
            // Resposta para validação (produto duplicado)
            return response()->json(['message' => $e->errors()['produto'][0]], 409);
        } catch (\Exception $e) {
            // Para outros erros, retorna erro genérico
            return response()->json(['message' => 'Erro ao criar produto', 'error' => $e->getMessage()], 500);
        }
    }
}
