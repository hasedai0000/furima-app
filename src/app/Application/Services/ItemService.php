<?php

namespace App\Application\Services;

use App\Application\Services\AuthenticationService;
use App\Application\Transformers\ItemTransformer;
use App\Domain\Item\Entities\Item as ItemEntity;
use App\Domain\Item\Repositories\ItemCategoryRepositoryInterface;
use App\Domain\Item\Repositories\ItemRepositoryInterface;
use App\Domain\Item\Services\LikeService;
use App\Domain\Item\ValueObjects\ItemImgUrl;
use Illuminate\Support\Str;

class ItemService
{
  private ItemRepositoryInterface $itemRepository;
  private ItemCategoryRepositoryInterface $itemCategoryRepository;
  private AuthenticationService $authService;
  private LikeService $likeService;

  public function __construct(
    ItemRepositoryInterface $itemRepository,
    ItemCategoryRepositoryInterface $itemCategoryRepository,
    AuthenticationService $authService,
    LikeService $likeService
  ) {
    $this->itemRepository = $itemRepository;
    $this->itemCategoryRepository = $itemCategoryRepository;
    $this->authService = $authService;
    $this->likeService = $likeService;
  }

  /**
   * 商品一覧を取得（自分が出品した商品は除外）
   *
   * @return array
   */
  public function getItems(string $searchTerm): array
  {
    // ログインユーザーがいる場合は、そのユーザーが出品した商品を除外
    if ($this->authService->isAuthenticated()) {
      $items = $this->itemRepository->findAllExcludingUser($this->authService->getCurrentUserId(), $searchTerm);
    } else {
      // 未ログインの場合は全ての商品を取得
      $items = $this->itemRepository->findAll($searchTerm);
    }

    return ItemTransformer::transformItems($items);
  }

  /**
   * マイリストの商品を取得
   *
   * @param string $searchTerm
   * @return array
   */
  public function getMyListItems(string $searchTerm): array
  {
    $userId = $this->authService->requireAuthentication();
    $items = $this->itemRepository->findMyListItems($userId, $searchTerm);

    return ItemTransformer::transformItems($items);
  }

  /**
   * 自分が出品した商品を取得
   *
   * @param string $searchTerm
   * @return array
   */
  public function getMySellItems(string $searchTerm): array
  {
    $userId = $this->authService->requireAuthentication();
    $items = $this->itemRepository->findMySellItems($userId, $searchTerm);

    return ItemTransformer::transformItems($items);
  }

  /**
   * 自分が購入した商品を取得
   *
   * @param string $searchTerm
   * @return array
   */
  public function getMyBuyItems(string $searchTerm): array
  {
    $userId = $this->authService->requireAuthentication();
    $items = $this->itemRepository->findMyBuyItems($userId, $searchTerm);

    return ItemTransformer::transformItems($items);
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

  /**
   * 商品詳細を取得（いいね状態を含む）
   *
   * @param string $id
   * @return array
   */
  public function getItemWithLikeStatus(string $id): array
  {
    $item = $this->itemRepository->findById($id);
    $itemArray = $item->toArray();

    // ログインユーザーがいいねしているかどうかを追加
    if ($this->authService->isAuthenticated()) {
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
    if (! empty($categoryIds)) {
      $this->itemCategoryRepository->attachCategories($item->getId(), $categoryIds);
    }

    return $item;
  }
}
