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
    /**
     * 取引が完了した商品を取得（購入した商品）
     *
     * @param string $userId
     * @param string $searchTerm
     * @return array
     */
    public function findMyCompletedBuyItems(string $userId, string $searchTerm): array;
    /**
     * 取引中の商品を取得（購入はしているが取引が完了していない）
     *
     * @param string $userId
     * @param string $searchTerm
     * @return array
     */
    public function findMyActiveTransactions(string $userId, string $searchTerm): array;
    public function findById(string $id): ?ItemEntity;
    public function save(ItemEntity $item): void;
}
