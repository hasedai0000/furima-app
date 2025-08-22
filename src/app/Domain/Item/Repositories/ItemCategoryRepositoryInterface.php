<?php

namespace App\Domain\Item\Repositories;

interface ItemCategoryRepositoryInterface
{
    public function attachCategories(string $itemId, array $categoryIds): void;
}
