<?php
namespace App\Http\Controllers;

use App\Http\Requests\ListPedidoRequest;
use App\Http\Requests\UpdatePedidoStatusRequest;
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
    
    public function status(int $id): JsonResponse
    {
        try {
            $status = $this->service->getStatus($id);

            return response()->json([
                'pedido_id' => $id,
                'status' => $status
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'mensagem' => 'Erro ao consultar status do pedido',
                'erro' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updatePedido(UpdatePedidoStatusRequest $request, int $id): JsonResponse
    {
        try{
            $pedido = $this->service->updatePedido(
                $id,
                $request->input('status')
            );
            return response()->json([
                'mensagem' => 'Status do pedido atualizado com sucesso',
                'pedido' => $pedido
            ]);
        }catch (\Throwable $e) {
            return response()->json([
                'mensagem' => 'Erro ao consultar status do pedido',
                'erro' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
 