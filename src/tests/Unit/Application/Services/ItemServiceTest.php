<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\AuthenticationService;
use App\Application\Services\ItemService;
use App\Domain\Item\Entities\Item as ItemEntity;
use App\Domain\Item\Repositories\ItemCategoryRepositoryInterface;
use App\Domain\Item\Repositories\ItemRepositoryInterface;
use App\Domain\Item\Services\LikeService;
use App\Domain\Item\ValueObjects\ItemImgUrl;
use Mockery;
use Tests\TestCase;

class ItemServiceTest extends TestCase
{
 private ItemService $itemService;
 private ItemRepositoryInterface $mockItemRepository;
 private ItemCategoryRepositoryInterface $mockItemCategoryRepository;
 private AuthenticationService $mockAuthService;
 private LikeService $mockLikeService;

 protected function setUp(): void
 {
  parent::setUp();
  $this->mockItemRepository = Mockery::mock(ItemRepositoryInterface::class);
  $this->mockItemCategoryRepository = Mockery::mock(ItemCategoryRepositoryInterface::class);
  $this->mockAuthService = Mockery::mock(AuthenticationService::class);
  $this->mockLikeService = Mockery::mock(LikeService::class);

  $this->itemService = new ItemService(
   $this->mockItemRepository,
   $this->mockItemCategoryRepository,
   $this->mockAuthService,
   $this->mockLikeService
  );
 }

 protected function tearDown(): void
 {
  Mockery::close();
  parent::tearDown();
 }

 /**
  * @test
  * getItems() 事前条件検証: searchTermは文字列
  * 事後条件検証: 配列を返す
  */
 public function getItems_retrieves_all_items_for_unauthenticated_user()
 {
  $searchTerm = 'test';
  $mockItems = [
   [
    'id' => '1',
    'name' => 'Item 1',
    'brand_name' => 'Brand 1',
    'description' => 'Description 1',
    'price' => 1000,
    'condition' => 'new',
    'img_url' => 'storage/item1.jpg',
    'purchases' => []
   ],
   [
    'id' => '2',
    'name' => 'Item 2',
    'brand_name' => 'Brand 2',
    'description' => 'Description 2',
    'price' => 2000,
    'condition' => 'used',
    'img_url' => 'storage/item2.jpg',
    'purchases' => []
   ],
   [
    'id' => '3',
    'name' => 'Item 3',
    'brand_name' => 'Brand 3',
    'description' => 'Description 3',
    'price' => 3000,
    'condition' => 'new',
    'img_url' => 'storage/item3.jpg',
    'purchases' => []
   ]
  ];

  // 認証サービスのモック設定
  $this->mockAuthService
   ->shouldReceive('isAuthenticated')
   ->once()
   ->andReturn(false);

  // アイテムリポジトリのモック設定
  $this->mockItemRepository
   ->shouldReceive('findAll')
   ->once()
   ->with($searchTerm)
   ->andReturn($mockItems);

  $result = $this->itemService->getItems($searchTerm);

  // 事後条件: 配列が返される
  $this->assertIsArray($result);
 }

 /**
  * @test
  * createItem() カテゴリが空の場合でも商品を作成できること
  */
 public function createItem_creates_item_with_empty_categories()
 {
  $userId = '123e4567-e89b-12d3-a456-426614174000';
  $categoryIds = [];

  $this->mockItemRepository
   ->shouldReceive('save')
   ->once()
   ->with(Mockery::type(ItemEntity::class));

  // カテゴリが空の場合はattachCategoriesは呼ばれない
  $this->mockItemCategoryRepository->shouldNotReceive('attachCategories');

  $result = $this->itemService->createItem(
   $userId,
   'Test Item',
   'Test Brand',
   'Description',
   1000,
   'new',
   'storage/test.jpg',
   $categoryIds
  );

  // 事後条件: ItemEntityが返される
  $this->assertInstanceOf(ItemEntity::class, $result);
 }

 /**
  * @test
  * 事前条件検証: 価格は正の整数でなければならない
  */
 public function createItem_throws_exception_for_negative_price()
 {
  $userId = '123e4567-e89b-12d3-a456-426614174000';
  $negativePrice = -1000;

  // 負の価格でItemEntityを作成しようとすると例外が発生することを期待
  $this->expectException(\Exception::class);

  $this->itemService->createItem(
   $userId,
   'Test Item',
   'Test Brand',
   'Description',
   $negativePrice,
   'new',
   'storage/test.jpg',
   []
  );
 }
}
