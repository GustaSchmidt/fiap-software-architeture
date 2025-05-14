<?php
namespace App\Http\Controllers;

use App\Http\Requests\ListPedidoRequest;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;

class PedidoController extends Controller
{
    public function __construct(private PedidoService $service) {}

    public function list(ListPedidoRequest $request): JsonResponse
    {
        try {
            $pedidos = $this->service->listarPedidos(
                $request->input('loja_id'),
                $request->input('filtro', [])
            );

            return response()->json($pedidos);
        } catch (\Throwable $e) {
            return response()->json([
                'mensagem' => 'Erro ao listar pedidos',
                'erro' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
