<?php

namespace Tests\Helpers;

use Mockery;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;
use App\Models\Category;
use App\Models\Purchase;
use App\Application\Services\ItemService;
use App\Application\Services\ProfileService;
use App\Application\Services\PurchaseService;
use App\Application\Services\AuthenticationService;
use App\Application\Services\FileUploadService;
use Illuminate\Foundation\Application;

/**
 * テスト用のモックヘルパークラス
 * データベースに依存しないテストを実現するためのユーティリティ
 */
class MockHelper
{
  /**
   * @var Application
   */
  private Application $app;

  public function __construct(Application $app)
  {
    $this->app = $app;
  }

  /**
   * PurchaseServiceのモックを作成
   */
  public function mockPurchaseService(array $methods = []): Mockery\MockInterface
  {
    $mock = Mockery::mock(PurchaseService::class);

    // デフォルトの動作を設定
    $mock->shouldReceive('createCheckoutSession')
      ->andReturn('https://checkout.stripe.com/pay/test_session');

    $mock->shouldReceive('purchase')
      ->andReturn($this->createMockPurchase());

    $mock->shouldReceive('completePurchase')
      ->andReturn($this->createMockPurchase());

    // カスタムメソッドの設定
    foreach ($methods as $method => $returnValue) {
      $mock->shouldReceive($method)->andReturn($returnValue);
    }

    $this->app->instance(PurchaseService::class, $mock);

    return $mock;
  }

  /**
   * ItemServiceのモックを作成
   */
  public function mockItemService(array $methods = []): Mockery\MockInterface
  {
    $mock = Mockery::mock(ItemService::class);

    $mock->shouldReceive('getItems')
      ->andReturn(collect([]));

    $mock->shouldReceive('getItemById')
      ->andReturn($this->createMockItem());

    $mock->shouldReceive('createItem')
      ->andReturn($this->createMockItem());

    // カスタムメソッドの設定
    foreach ($methods as $method => $returnValue) {
      $mock->shouldReceive($method)->andReturn($returnValue);
    }

    $this->app->instance(ItemService::class, $mock);

    return $mock;
  }

  /**
   * ProfileServiceのモックを作成
   */
  public function mockProfileService(array $methods = []): Mockery\MockInterface
  {
    $mock = Mockery::mock(ProfileService::class);

    $mock->shouldReceive('getProfile')
      ->andReturn($this->createMockProfile());

    $mock->shouldReceive('createOrUpdateProfile')
      ->andReturn($this->createMockProfile());

    // カスタムメソッドの設定
    foreach ($methods as $method => $returnValue) {
      $mock->shouldReceive($method)->andReturn($returnValue);
    }

    $this->app->instance(ProfileService::class, $mock);

    return $mock;
  }

  /**
   * FileUploadServiceのモックを作成
   */
  public function mockFileUploadService(array $methods = []): Mockery\MockInterface
  {
    $mock = Mockery::mock(FileUploadService::class);

    $mock->shouldReceive('uploadImage')
      ->andReturn('uploads/test-image.jpg');

    // カスタムメソッドの設定
    foreach ($methods as $method => $returnValue) {
      $mock->shouldReceive($method)->andReturn($returnValue);
    }

    $this->app->instance(FileUploadService::class, $mock);

    return $mock;
  }

  /**
   * AuthenticationServiceのモックを作成
   */
  public function mockAuthenticationService(array $methods = []): Mockery\MockInterface
  {
    $mock = Mockery::mock(AuthenticationService::class);

    $mock->shouldReceive('getCurrentUser')
      ->andReturn($this->createMockUser());

    // カスタムメソッドの設定
    foreach ($methods as $method => $returnValue) {
      $mock->shouldReceive($method)->andReturn($returnValue);
    }

    $this->app->instance(AuthenticationService::class, $mock);

    return $mock;
  }

  /**
   * モックユーザーを作成
   */
  public function createMockUser(array $attributes = []): array
  {
    return array_merge([
      'id' => 'test-user-id',
      'name' => 'テストユーザー',
      'email' => 'test@example.com',
      'email_verified_at' => now(),
      'created_at' => now(),
      'updated_at' => now(),
    ], $attributes);
  }

  /**
   * モックアイテムを作成
   */
  public function createMockItem(array $attributes = []): array
  {
    return array_merge([
      'id' => 'test-item-id',
      'name' => 'テスト商品',
      'brand_name' => 'テストブランド',
      'description' => 'これはテスト商品です',
      'price' => 1000,
      'condition' => 'good',
      'img_url' => 'uploads/test-item.jpg',
      'user_id' => 'test-user-id',
      'sold_at' => null,
      'created_at' => now(),
      'updated_at' => now(),
    ], $attributes);
  }

  /**
   * モックプロフィールを作成
   */
  public function createMockProfile(array $attributes = []): array
  {
    return array_merge([
      'id' => 'test-profile-id',
      'user_id' => 'test-user-id',
      'img_url' => 'uploads/test-profile.jpg',
      'postcode' => '123-4567',
      'address' => '東京都渋谷区テスト町1-1-1',
      'building_name' => 'テストビル101',
      'created_at' => now(),
      'updated_at' => now(),
    ], $attributes);
  }

  /**
   * モックカテゴリを作成
   */
  public function createMockCategory(array $attributes = []): array
  {
    return array_merge([
      'id' => 'test-category-id',
      'name' => 'テストカテゴリ',
      'created_at' => now(),
      'updated_at' => now(),
    ], $attributes);
  }

  /**
   * モック購入を作成
   */
  public function createMockPurchase(array $attributes = []): \App\Domain\Purchase\Entities\Purchase
  {
    return new \App\Domain\Purchase\Entities\Purchase(
      $attributes['id'] ?? 'test-purchase-id',
      $attributes['user_id'] ?? 'test-user-id',
      $attributes['item_id'] ?? 'test-item-id',
      $attributes['payment_method'] ?? 'convenience_store',
      new \App\Domain\Purchase\ValueObjects\PurchasePostCode($attributes['postcode'] ?? '123-4567'),
      $attributes['address'] ?? '東京都渋谷区テスト町1-1-1',
      $attributes['building_name'] ?? 'テストビル101'
    );
  }

  /**
   * Stripeセッションのモックを作成
   */
  public function mockStripeSession(string $paymentStatus = 'paid'): void
  {
    $sessionMock = Mockery::mock();
    $sessionMock->payment_status = $paymentStatus;

    $stripeMock = Mockery::mock('alias:Stripe\Stripe');
    $stripeMock->shouldReceive('setApiKey')->once();

    $checkoutMock = Mockery::mock('alias:Stripe\Checkout\Session');
    $checkoutMock->shouldReceive('retrieve')
      ->andReturn($sessionMock);
  }

  /**
   * 例外を発生させるPurchaseServiceのモックを作成
   */
  public function mockPurchaseServiceWithException(string $message = '決済エラーが発生しました'): Mockery\MockInterface
  {
    $mock = Mockery::mock(PurchaseService::class);
    $mock->shouldReceive('createCheckoutSession')
      ->andThrow(new \Exception($message));

    $this->app->instance(PurchaseService::class, $mock);

    return $mock;
  }

  /**
   * すべてのモックをクリアする
   */
  public function clearAllMocks(): void
  {
    Mockery::close();
  }
}
