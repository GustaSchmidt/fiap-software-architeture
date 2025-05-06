<?php
namespace App\Domain\Repositories;
use App\Domain\Entities\Client;

interface ClientRepositoryInterface
{
    public function save(Client $client): Client;
}