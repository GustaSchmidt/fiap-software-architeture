<?php

namespace App\Services;

use App\Domain\Entities\Loja as LojaEntity;
use App\Domain\Repositories\LojaRepositoryInterface;

class LojaService
{
    public function __construct(
        protected LojaRepositoryInterface $lojaRepository
    ) {}

    public function criar(array $data)
    {
        $loja = new LojaEntity($data['nome'], $data['endereco']);
        return $this->lojaRepository->save($loja);
    }
}
