<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    public function save(Product $product): Product;
    public function findById(int $id): ?Product;
    public function findByCategory(?string $categoria): array;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}

