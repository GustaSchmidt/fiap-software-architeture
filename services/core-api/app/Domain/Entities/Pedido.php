<?php

namespace App\Domain\Entities;

class Pedido
{
    public function __construct(
        public ?int $id,
        public int $client_id,
        public int $sacola_id,
        public string $status,
        public float $total,
        public ?string $mercado_pago_id = null
    ) {}
}
