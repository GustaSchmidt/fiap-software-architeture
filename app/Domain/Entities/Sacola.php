<?php

namespace App\Domain\Entities;

namespace App\Domain\Entities;

class Sacola
{
    public function __construct(
        public int $id,
        public int $client_id,
        public array $produtos,
        public float $total
    ) {}
}

