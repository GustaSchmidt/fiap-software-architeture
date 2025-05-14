<?php

namespace App\Http\Controllers;

use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Http\Requests\AddItemToSacolaRequest;
use App\Services\SacolaService;
use Illuminate\Http\JsonResponse;
use App\Models\Client;
use App\Domain\Entities\Sacola;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\RemoveItemFromSacolaRequest;

class SacolaController extends Controller
{
    private SacolaService $sacolaService;
    private SacolaRepositoryInterface $sacolaRepository;

    public function __construct(
        SacolaService $sacolaService,
        SacolaRepositoryInterface $sacolaRepository
    ) {
        $this->sacolaService = $sacolaService;
        $this->sacolaRepository = $sacolaRepository;
    }

    public function adicionarItem(AddItemToSacolaRequest $request): JsonResponse
    {
        try {
            $clienteId = $request->input('client_id');
            $produtoId = $request->input('produto_id');
            $quantidade = $request->input('quantidade');

            $cliente = Client::findOrFail($clienteId);

            $sacolaModel = \App\Models\Sacola::firstOrCreate(
                ['client_id' => $clienteId, 'status' => 'ativa'],
                []
            );

            $this->sacolaService->adicionarItem($clienteId, $produtoId, $quantidade);

            return response()->json(['mensagem' => 'Item adicionado à sacola com sucesso.']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'mensagem' => 'Cliente não encontrado.',
                'erro' => $e->getMessage(),
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'mensagem' => 'Erro ao adicionar item à sacola',
                'erro' => $e->getMessage(),
            ], 500);
        }
    }

    public function listarPorCliente(int $clientId): JsonResponse
    {
        try {
            $cliente = Client::findOrFail($clientId);
            $sacolas = $this->sacolaRepository->listarPorCliente($clientId);
            return response()->json($sacolas);
        } catch (ModelNotFoundException $e) {
             return response()->json([
                'mensagem' => 'Cliente não encontrado ao tentar listar sacolas.',
                'erro' => $e->getMessage(),
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'mensagem' => 'Erro ao listar sacolas do cliente',
                'erro' => $e->getMessage(),
            ], 500);
        }
    }

    public function remove(RemoveItemFromSacolaRequest $request): JsonResponse
    {
        try {
            $this->sacolaService->removerItem(
                $request->input('client_id'),
                $request->input('produto_id')
            );

            return response()->json(['mensagem' => 'Item removido com sucesso']);
        } catch (\Throwable $e) {
            return response()->json([
                'mensagem' => 'Erro ao remover item da sacola',
                'erro' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

}