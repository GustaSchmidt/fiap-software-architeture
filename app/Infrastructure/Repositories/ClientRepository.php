<?php
namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Client as DomainClient;
use App\Domain\Repositories\ClientRepositoryInterface;
use App\Models\Client;

class ClientRepository implements ClientRepositoryInterface
{
    public function save(DomainClient $domainClient): DomainClient
    {
        // Lógica de salvar ou atualizar
        $client = Client::find($domainClient->id);

        if ($client) {
            $client->nome = $domainClient->nome;
            $client->sobrenome = $domainClient->sobrenome;
            $client->email = $domainClient->email;
            $client->cpf = $domainClient->cpf;
            $client->senha = $domainClient->senha;
            $client->save();
        } else {
            // Caso não encontre, cria um novo
            $client = new Client([
                'nome' => $domainClient->nome,
                'sobrenome' => $domainClient->sobrenome,
                'email' => $domainClient->email,
                'cpf' => $domainClient->cpf,
                'senha' => $domainClient->senha,
            ]);
            $client->save();
        }

        // Atualiza o ID do cliente e retorna o cliente
        $domainClient->id = $client->id;
        return $domainClient;
    }

    public function findById(string $id): ?DomainClient
    {
        $client = Client::find($id);

        if (!$client) {
            return null;
        }

        return new DomainClient(
            id: $client->id,
            nome: $client->nome,
            sobrenome: $client->sobrenome,
            email: $client->email,
            cpf: $client->cpf,
            senha: $client->senha
        );
    }

    public function findByCpf(string $cpf): ?DomainClient
    {
        $client = Client::where('cpf', $cpf)->first();

        if (!$client) {
            return null;
        }

        return new DomainClient(
            id: $client->id,
            nome: $client->nome,
            sobrenome: $client->sobrenome,
            email: $client->email,
            cpf: $client->cpf,
            senha: $client->senha
        );
    }
}
