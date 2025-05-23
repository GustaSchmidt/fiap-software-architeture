<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
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

    public function update(UpdateClientRequest $request): JsonResponse
    {
        // Validação já feita pela UpdateClientRequest

        // Recuperando os dados validados
        $data = $request->validated();

        // Chamando o serviço para atualizar o cliente
        $client = $this->service->updateClient($data);

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado ou erro ao atualizar'], 404);
        }

        return response()->json([
            'message' => 'Cliente atualizado com sucesso',
            'client' => $client
        ], 200);
    }

    public function delete(Request $request): JsonResponse
    {
        $id = (int) $request->query('id');

        if (!$id) {
            return response()->json(['message' => 'ID é obrigatório'], 400);
        }

        $deleted = $this->service->deleteClient($id);

        if (!$deleted) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }

        return response()->json(['message' => 'Cliente deletado com sucesso']);
    }
}

