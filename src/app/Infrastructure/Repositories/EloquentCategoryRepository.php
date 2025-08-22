<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Item\Entities\Category as CategoryEntity;
use App\Domain\Item\Repositories\CategoryRepositoryInterface;
use App\Models\Category;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * 全てのカテゴリーを取得
     *
     * @return array
     */
    public function findAll(): array
    {
        return Category::all()->toArray();
    }

    /**
     * IDでカテゴリーを取得
     *
     * @param string $id
     * @return CategoryEntity|null
     */
    public function findById(string $id): ?CategoryEntity
    {
        $eloquentCategory = Category::find($id);

        if (! $eloquentCategory) {
            return null;
        }

        return new CategoryEntity(
            $eloquentCategory->id,
            $eloquentCategory->name
        );
    }
}
