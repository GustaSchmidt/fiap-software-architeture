<?php

namespace App\Domain\Entities;

class Sacola
{
    public function __construct(
        public int $id,
        public int $client_id,
        public string $status,
        public array $produtos,
        public float $total
    ) {}
}

