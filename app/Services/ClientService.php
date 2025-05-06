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
}
