<?php

namespace App\Services;

use App\Domain\Entities\Loja as LojaEntity;
use App\Domain\Repositories\LojaRepositoryInterface;

class LojaService
{
    private LojaRepositoryInterface $lojaRepository;

    public function __construct(LojaRepositoryInterface $lojaRepository)
    {
        $this->lojaRepository = $lojaRepository;
    }

    public function criar(array $data)
    {
        $loja = new LojaEntity($data['nome'], $data['endereco']);
        return $this->lojaRepository->save($loja);
    }

    public function searchByName(string $nome)
    {
        return $this->lojaRepository->findByName($nome);
    }
}
