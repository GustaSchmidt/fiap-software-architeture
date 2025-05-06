<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function __construct(private ClientService $service) {}

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->service->createClient($request->validated());

        return response()->json([
            'id' => $client->id,
            'message' => 'Cliente criado com sucesso',
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $client = $this->service->getClientById($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }

        return response()->json([
            'id' => $client->id,
            'nome' => $client->nome,
            'sobrenome' => $client->sobrenome,
            'email' => $client->email,
            'cpf' => $client->cpf,
        ]);
    }

    public function searchByCpf(Request $request): JsonResponse
    {
        $request->validate([
            'cpf' => 'required|string|cpf',  // Aqui você pode usar uma validação customizada para CPF
        ]);

        $client = $this->service->findClientByCpf($request->input('cpf'));

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }

        return response()->json([
            'id' => $client->id,
            'nome' => $client->nome,
            'sobrenome' => $client->sobrenome,
            'email' => $client->email,
            'cpf' => $client->cpf,
        ]);
    }
}

