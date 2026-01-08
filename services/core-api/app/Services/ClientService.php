<?php

namespace App\Services;

use App\Domain\Entities\Client;
use App\Domain\Repositories\ClientRepositoryInterface;

class ClientService
{
    public function __construct(private ClientRepositoryInterface $repository) {}

    public function createClient(array $data): Client
    {
        $client = new Client(
            id: null,
            nome: $data['nome'],
            sobrenome: $data['sobrenome'],
            email: $data['email'],
            cpf: $data['cpf'],
            senha: $data['senha']
        );

        return $this->repository->save($client);
    }

    public function getClientById(string $id): ?Client
    {
        return $this->repository->findById($id);
    }
    public function findClientByCpf(string $cpf): ?Client
    {
        return $this->repository->findByCpf($cpf);
    }

    public function updateClient(array $data): ?Client
    {
        // Verificar se o cliente existe
        $client = $this->repository->findById($data['id']);
        if (!$client) {
            return null;
        }

        // Atualizar os dados do cliente
        $client->nome = $data['nome'] ?? $client->nome;
        $client->sobrenome = $data['sobrenome'] ?? $client->sobrenome;
        $client->email = $data['email'] ?? $client->email;
        $client->cpf = $data['cpf'] ?? $client->cpf;
        $client->senha = $data['senha'] ?? $client->senha;

        // Salvar as alterações
        return $this->repository->save($client);
    }

    public function deleteClient(int $id): bool
    {
        return $this->repository->deleteById($id);
    }
}
