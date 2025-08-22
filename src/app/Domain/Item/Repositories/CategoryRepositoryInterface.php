<?php

namespace App\Domain\Item\Repositories;

use App\Domain\Item\Entities\Category as CategoryEntity;

interface CategoryRepositoryInterface
{
    public function findAll(): array;
    public function findById(string $id): ?CategoryEntity;
}
