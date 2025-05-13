<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddItemToSacolaRequest;
use App\Services\SacolaService;
use Illuminate\Http\JsonResponse;
use App\Models\Sacola;
use App\Models\Client;

class SacolaController extends Controller
{
    public function __construct(private SacolaService $sacolaService) {}

    public function adicionarItem(AddItemToSacolaRequest $request): JsonResponse
    {
        try {
            $clienteId = $request->input('client_id');
            $produtoId = $request->input('produto_id');
            $quantidade = $request->input('quantidade');

            // Buscar o cliente
            $cliente = Client::findOrFail($clienteId);

            // Verificar se o cliente já tem uma sacola ativa, ou criar uma nova
            $sacola = $cliente->sacolas()->latest()->first();  // Assumindo que a sacola mais recente seja a ativa

            if (!$sacola) {
                // Criar nova sacola se não houver nenhuma ativa
                $sacola = $cliente->sacolas()->create();
            }

            // Chama o serviço para adicionar o item à sacola
            $this->sacolaService->adicionarItem($sacola->id, $produtoId, $quantidade);

            return response()->json(['mensagem' => 'Item adicionado à sacola com sucesso.']);
        } catch (\Throwable $e) {
            return response()->json([
                'mensagem' => 'Erro ao adicionar item à sacola',
                'erro' => $e->getMessage(),
            ], 500);
        }
    }
}
