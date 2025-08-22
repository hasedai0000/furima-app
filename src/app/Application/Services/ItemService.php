<?php

namespace App\Application\Services;

use App\Domain\Item\Repositories\ItemRepositoryInterface;
use App\Domain\Item\Services\LikeService;
use Illuminate\Support\Facades\Auth;
use App\Domain\Item\ValueObjects\ItemImgUrl;
use App\Domain\Item\Entities\Item as ItemEntity;
use App\Domain\Item\Repositories\ItemCategoryRepositoryInterface;
use App\Domain\Item\ValueObjects\ItemCondition;
use Illuminate\Support\Str;

class ItemService
{
  private $itemRepository;
  private $likeService;
  private $itemCategoryRepository;

  public function __construct(
    ItemRepositoryInterface $itemRepository,
    LikeService $likeService,
    ItemCategoryRepositoryInterface $itemCategoryRepository
  ) {
    $this->itemRepository = $itemRepository;
    $this->likeService = $likeService;
    $this->itemCategoryRepository = $itemCategoryRepository;
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
        'brandName' => $item['brand_name'],
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
    $itemArray = $item->toArray();

    // ログインユーザーがいいねしているかどうかを追加
    if (Auth::check()) {
      $itemArray['isLiked'] = $this->likeService->isLiked($id);
    } else {
      $itemArray['isLiked'] = false;
    }

    return $itemArray;
  }

  /**
   * 商品を出品する
   *
   * @param string $userId
   * @param string $name
   * @param string $brandName
   * @param string $description
   * @param int $price
   * @param string $condition
   * @param string $imgUrl
   * @param array $categoryIds
   * @return ItemEntity
   */
  public function createItem(
    string $userId,
    string $name,
    string $brandName,
    string $description,
    int $price,
    string $condition,
    string $imgUrl,
    array $categoryIds
  ): ItemEntity {
    $itemImgUrl = new ItemImgUrl($imgUrl);

    $item = new ItemEntity(
      Str::uuid()->toString(),
      $userId,
      $name,
      $brandName,
      $description,
      $price,
      $condition,
      $itemImgUrl,
      false,
      $categoryIds,
      [],
      []
    );

    $this->itemRepository->save($item);

    // カテゴリーの関連付けを作成
    if (!empty($categoryIds)) {
      $this->itemCategoryRepository->attachCategories($item->getId(), $categoryIds);
    }

    return $item;
  }
}
