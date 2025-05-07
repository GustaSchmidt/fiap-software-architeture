<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(private ProductService $service) {}

    public function create(StoreProductRequest $request): JsonResponse
    {
        $product = $this->service->createProduct($request->validated());
        return response()->json(['message' => 'Produto criado com sucesso', 'id' => $product->id], 200);
    }
}

