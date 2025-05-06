<?php
namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Client as DomainClient;
use App\Domain\Repositories\ClientRepositoryInterface;
use App\Models\Client;

class ClientRepository implements ClientRepositoryInterface
{
    public function save(DomainClient $domainClient): DomainClient
    {
        $client = new Client([
            'nome' => $domainClient->nome,
            'sobrenome' => $domainClient->sobrenome,
            'email' => $domainClient->email,
            'cpf' => $domainClient->cpf,
            'senha' => $domainClient->senha,
        ]);
        $client->save();

        $domainClient->id = $client->id;

        return $domainClient;
    }
}
