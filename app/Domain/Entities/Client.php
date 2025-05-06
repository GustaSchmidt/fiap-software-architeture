<?php
namespace App\Domain\Entities;

class Client{
    public function __construct(
        public ?int $id,
        public string $nome,
        public string $sobrenome,
        public string $email,
        public string $cpf,
        public string $senha
    ) {}
}
