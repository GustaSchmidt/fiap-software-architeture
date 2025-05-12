<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Loja;

interface LojaRepositoryInterface
{
    public function save(Loja $loja): mixed;
    public function findByName(string $nome): array;
}
