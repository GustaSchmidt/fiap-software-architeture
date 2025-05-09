<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Loja as LojaEntity;
use App\Domain\Repositories\LojaRepositoryInterface;
use App\Models\Loja;

class LojaRepository implements LojaRepositoryInterface
{
    public function save(LojaEntity $lojaEntity): Loja
    {
        return Loja::create([
            'nome' => $lojaEntity->nome,
            'endereco' => $lojaEntity->endereco,
        ]);
    }
}
