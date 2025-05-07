<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreLojaRequest;
use App\Services\LojaService;
use Illuminate\Http\JsonResponse;

class LojaController extends Controller
{
    public function __construct(
        protected LojaService $lojaService
    ) {}

    public function store(StoreLojaRequest $request): JsonResponse
    {
        $loja = $this->lojaService->criar($request->validated());

        return response()->json([
            'message' => 'Loja criada com sucesso!',
            'data' => $loja,
        ], 201);
    }
}
