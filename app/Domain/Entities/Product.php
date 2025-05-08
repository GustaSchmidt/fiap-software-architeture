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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'preco' => $this->preco,
            'categoria' => $this->categoria,
            'ingredientes' => $this->ingredientes,
            'porcao' => $this->porcao,
            'informacoes_nutricionais' => $this->informacoes_nutricionais,
            'alergenicos' => $this->alergenicos,
            'loja_id' => $this->loja_id,
        ];
    }

}
