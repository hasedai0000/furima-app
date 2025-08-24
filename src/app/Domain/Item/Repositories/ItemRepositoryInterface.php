<?php

namespace App\Domain\Item\Repositories;

use App\Domain\Item\Entities\Item as ItemEntity;

interface ItemRepositoryInterface
{
    public function findAll(string $searchTerm): array;
    public function findAllExcludingUser(string $userId, string $searchTerm): array;
    public function findMyListItems(string $userId, string $searchTerm): array;
    public function findMySellItems(string $userId, string $searchTerm): array;
    public function findMyBuyItems(string $userId, string $searchTerm): array;
    public function findById(string $id): ?ItemEntity;
    public function save(ItemEntity $item): void;
}
