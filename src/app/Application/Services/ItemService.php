<?php

namespace App\Application\Services;

use App\Domain\Item\Repositories\ItemRepositoryInterface;

class ItemService
{
  private $itemRepository;

  public function __construct(
    ItemRepositoryInterface $itemRepository
  ) {
    $this->itemRepository = $itemRepository;
  }

  /**
   * 商品一覧を取得
   *
   * @return array
   */
  public function getItems(): array
  {
    $items = $this->itemRepository->findAll();

    $items = array_map(function ($item) {
      return [
        'id' => $item['id'],
        'name' => $item['name'],
        'description' => $item['description'],
        'price' => $item['price'],
        'condition' => $item['condition'],
        'imgUrl' => $item['img_url'],
      ];
    }, $items);

    return $items;
  }
}
