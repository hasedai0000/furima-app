<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Item\Entities\Item as ItemEntity;
use App\Domain\Item\Repositories\ItemRepositoryInterface;
use App\Models\Item;

class EloquentItemRepository implements ItemRepositoryInterface
{
  /**
   * 商品一覧を取得
   *
   * @return array
   */
  public function findAll(): array
  {
    return Item::all()->toArray();
  }
}
