<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Item\Entities\Item as ItemEntity;
use App\Domain\Item\Repositories\ItemRepositoryInterface;
use App\Models\Item;

class EloquentItemRepository implements ItemRepositoryInterface
{
  /**
   * 商品一覧を取得（購入済み商品も含む）
   *
   * @param string $searchTerm
   * @return array
   */
  public function findAll(string $searchTerm): array
  {
    return Item::with('purchases')
      ->where('name', 'LIKE', '%' . $searchTerm . '%')
      ->orderBy('created_at', 'desc')
      ->get()
      ->toArray();
  }

  /**
   * 指定されたユーザーが出品した商品を除外して商品一覧を取得
   *
   * @param string $userId
   * @param string $searchTerm
   * @return array
   */
  public function findAllExcludingUser(string $userId, string $searchTerm): array
  {
    return Item::with('purchases')
      ->where('name', 'LIKE', '%' . $searchTerm . '%')
      ->where('user_id', '!=', $userId)
      ->orderBy('created_at', 'desc')
      ->get()
      ->toArray();
  }

  /**
   * マイリストの商品を取得
   *
   * @param string $userId
   * @param string $searchTerm
   * @return array
   */
  public function findMyListItems(string $userId, string $searchTerm): array
  {
    return Item::with('purchases')
      ->whereHas('likes', function ($query) use ($userId) {
        $query->where('user_id', $userId);
      })
      ->where('name', 'LIKE', '%' . $searchTerm . '%')
      ->orderBy('created_at', 'desc')
      ->get()
      ->toArray();
  }
}
