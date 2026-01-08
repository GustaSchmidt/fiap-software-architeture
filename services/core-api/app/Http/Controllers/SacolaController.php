<?php
namespace App\Http\Controllers;

use App\Services\SacolaService;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CheckoutSacolaRequest;
use App\Http\Requests\AdicionarItemSacolaRequest;
use App\Http\Requests\ListarSacolaRequest;
use App\Http\Requests\RemoverItemSacolaRequest;
use App\Http\Requests\FecharSacolaRequest;

class SacolaController extends Controller
{
    public function __construct(
        private SacolaService $sacolaService,
        private CheckoutService $checkoutService
    ) {}

    /**
     * @OA\Post(
     * path="/api/sacolas/adicionar",
     * summary="Adiciona um item à sacola",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/AdicionarItemSacolaRequest")
     * ),
     * @OA\Response(response=200, description="Item adicionado com sucesso")
     * )
     */
    public function adicionarItem(AdicionarItemSacolaRequest $request): JsonResponse
    {
        try {
            $this->sacolaService->adicionarItem($request->input('client_id'), $request->input('produto_id'), $request->input('quantidade'));
            return response()->json(['message' => 'Item adicionado com sucesso'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao adicionar item'], 500);
        }
    }
    
    /**
     * @OA\Get(
     * path="/api/sacolas/listar/{client_id}",
     * summary="Lista itens na sacola de um cliente",
     * @OA\Parameter(
     * name="client_id",
     * in="path",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(response=200, description="Sacola listada com sucesso", @OA\JsonContent(ref="#/components/schemas/SacolaListResponse"))
     * )
     */
    public function listarPorCliente(int $client_id): JsonResponse
    {
        try {
            $sacola = $this->sacolaService->listarPorCliente($client_id);
            return response()->json(['data' => $sacola], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao listar sacola'], 500);
        }
    }
    
    /**
     * @OA\Delete(
     * path="/api/sacolas/remover",
     * summary="Remove um item da sacola",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/RemoverItemSacolaRequest")
     * ),
     * @OA\Response(response=200, description="Item removido com sucesso")
     * )
     */
    public function removerItem(RemoverItemSacolaRequest $request): JsonResponse
    {
        try {
            $this->sacolaService->removerItem($request->input('client_id'), $request->input('produto_id'));
            return response()->json(['message' => 'Item removido com sucesso'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao remover item'], 500);
        }
    }
    
    /**
     * @OA\Post(
     * path="/api/sacolas/fechar",
     * summary="Fecha a sacola e gera um pedido",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/CheckoutSacolaRequest")
     * ),
     * @OA\Response(response=200, description="Checkout realizado com sucesso", @OA\JsonContent(ref="#/components/schemas/CheckoutResponse"))
     * )
     */
    public function checkout(CheckoutSacolaRequest $request): JsonResponse
    {
        try {
            $data = $this->checkoutService->processarCheckout($request->input('client_id'));

            return response()->json([
                'mensagem' => 'Checkout realizado com sucesso',
                'pedido' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'mensagem' => 'Erro ao processar checkout',
                'erro' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    
    /**
     * @OA\Post(
     * path="/api/sacolas/fechar-pedido",
     * summary="Fecha o pedido",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/FecharSacolaRequest")
     * ),
     * @OA\Response(response=200, description="Pedido fechado com sucesso")
     * )
     */
    public function fecharSacola(FecharSacolaRequest $request): JsonResponse
    {
        try {
            $this->sacolaService->fecharSacola($request->input('id_sacola'), $request->input('status_final'));
            return response()->json(['message' => 'Pedido fechado com sucesso'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao fechar pedido'], 500);
        }
    }
}