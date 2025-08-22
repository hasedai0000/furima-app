<?php

namespace App\Domain\Item\Services;

use App\Domain\Item\Repositories\CategoryRepositoryInterface;

class CategoryService
{
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getCategories(): array
    {
        $categories = $this->categoryRepository->findAll();

        $categories = array_map(function ($category) {
            return [
                'id' => $category['id'],
                'name' => $category['name'],
            ];
        }, $categories);

        return $categories;
    }
}
