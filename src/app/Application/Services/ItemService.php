<?php

namespace App\Application\Services;

use App\Domain\Item\Repositories\ItemRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ItemService
{
  private $itemRepository;

  public function __construct(
    ItemRepositoryInterface $itemRepository
  ) {
    $this->itemRepository = $itemRepository;
  }

  /**
   * 商品一覧を取得（自分が出品した商品は除外）
   *
   * @return array
   */
  public function getItems(string $searchTerm): array
  {
    // ログインユーザーがいる場合は、そのユーザーが出品した商品を除外
    if (Auth::check()) {
      $items = $this->itemRepository->findAllExcludingUser(Auth::id(), $searchTerm);
    } else {
      // 未ログインの場合は全ての商品を取得
      $items = $this->itemRepository->findAll($searchTerm);
    }

    $items = array_map(function ($item) {
      return [
        'id' => $item['id'],
        'name' => $item['name'],
        'description' => $item['description'],
        'price' => $item['price'],
        'condition' => $item['condition'],
        'imgUrl' => $item['img_url'],
        'isSold' => isset($item['purchases']) && count($item['purchases']) > 0,
      ];
    }, $items);

    return $items;
  }

  /**
   * マイリストの商品を取得
   *
   * @param string $searchTerm
   * @return array
   */
  public function getMyListItems(string $searchTerm): array
  {
    $items = $this->itemRepository->findMyListItems(Auth::id(), $searchTerm);

    $items = array_map(function ($item) {
      return [
        'id' => $item['id'],
        'name' => $item['name'],
        'description' => $item['description'],
        'price' => $item['price'],
        'condition' => $item['condition'],
        'imgUrl' => $item['img_url'],
        'isSold' => isset($item['purchases']) && count($item['purchases']) > 0,
      ];
    }, $items);

    return $items;
  }

  /**
   * 商品詳細を取得
   *
   * @param string $id
   * @return array
   */
  public function getItem(string $id): array
  {
    $item = $this->itemRepository->findById($id);
    return $item->toArray();
  }
}
