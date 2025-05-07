<?php
namespace App\Domain\Entities;

class Product
{
    public function __construct(
        public ?int $id,
        public string $nome,
        public float $preco,
        public string $categoria,
        public array $ingredientes,
        public string $porcao,
        public array $informacoes_nutricionais,
        public ?string $alergenicos,
        public int $loja_id
    ) {}
}
