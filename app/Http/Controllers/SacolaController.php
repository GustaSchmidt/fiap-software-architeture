<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddItemToSacolaRequest;
use App\Services\SacolaService;
use Illuminate\Http\JsonResponse;

class SacolaController extends Controller
{
    protected SacolaService $service;

    public function __construct(SacolaService $service)
    {
        $this->service = $service;
    }

    public function add(AddItemToSacolaRequest $request): JsonResponse
    {
        try {
            // Chama o serviço para adicionar o item à sacola
            $sacolaId = $this->service->addItemToSacola(
                $request->input('cliente_id'),
                $request->input('produto_id'),
                $request->input('quantidade')
            );

            return response()->json([
                'message' => 'Item adicionado à sacola',
                'sacola_id' => $sacolaId,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erro ao adicionar item à sacola',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
