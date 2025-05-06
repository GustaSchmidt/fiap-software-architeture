<?php
namespace App\Adapters\Repositories;

use App\Domain\Entities\Client as DomainClient;
use App\Domain\Repositories\ClientRepositoryInterface;
use App\Models\Client;

class ClientRepository implements ClientRepositoryInterface
{
    public function save(DomainClient $domainClient): DomainClient
    {
        $client = Client::create([
            'nome' => $domainClient->nome,
            'sobrenome' => $domainClient->sobrenome,
            'email' => $domainClient->email,
            'cpf' => $domainClient->cpf,
            'senha' => $domainClient->senha,
        ]);

        $domainClient->id = $client->id;
        return $domainClient;
    }
}
