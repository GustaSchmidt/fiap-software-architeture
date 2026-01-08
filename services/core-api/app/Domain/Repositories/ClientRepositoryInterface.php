<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Client;

interface ClientRepositoryInterface
{
    public function save(Client $client): Client;
    public function findById(string $id): ?Client;
    public function findByCpf(string $cpf): ?Client;
    public function deleteById(int $id): bool;
}