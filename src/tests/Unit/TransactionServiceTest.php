<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Application\Services\TransactionService;
use App\Application\Services\AuthenticationService;
use App\Domain\Transaction\Entities\Transaction as TransactionEntity;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Models\User;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;
use Mockery;

/**
 * TransactionServiceのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 */
class TransactionServiceTest extends TestCase
{
  protected function setUp(): void
  {
    parent::setUp();
    Mail::fake();
  }

  protected function tearDown(): void
  {
    parent::tearDown();
    Mockery::close();
  }

  /**
   * @test
   * getTransaction メソッドの正常取得契約テスト
   * 
   * 事前条件: 認証済みユーザー、取引が存在し、ユーザーが取引に関与している
   * 事後条件: TransactionEntityが返される
   * 不変条件: 返されるエンティティの整合性が保たれる
   */
  public function test_get_transaction_returns_entity_with_correct_contract()
  {
    // 事前条件: 認証済みユーザーと取引データ
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $seller->id]);
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id,
      'status' => 'active'
    ]);

    // モックの設定
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $transactionEntity = new TransactionEntity(
      $transaction->id,
      $transaction->item_id,
      $transaction->buyer_id,
      $transaction->seller_id,
      $transaction->status,
      $transaction->completed_at ? new \DateTime($transaction->completed_at->format('Y-m-d H:i:s')) : null
    );

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andReturn($buyer->id);

    $transactionRepository->shouldReceive('findById')
      ->withAnyArgs()
      ->once()
      ->andReturn($transactionEntity);

    $service = new TransactionService($transactionRepository, $authService);

    // Act: 取引取得
    $result = $service->getTransaction($transaction->id);

    // 事後条件の検証
    $this->assertInstanceOf(TransactionEntity::class, $result);
    $this->assertEquals($transaction->id, $result->getId());
    $this->assertEquals($transaction->buyer_id, $result->getBuyerId());
    $this->assertEquals($transaction->seller_id, $result->getSellerId());

    // 不変条件: エンティティの整合性
    $this->assertEquals($transaction->item_id, $result->getItemId());
    $this->assertEquals($transaction->status, $result->getStatus());
  }

  /**
   * @test
   * getTransaction メソッドの取引不存在契約テスト
   * 
   * 事前条件: 認証済みユーザー、存在しない取引ID
   * 事後条件: nullが返される
   * 不変条件: 例外が発生しない
   */
  public function test_get_transaction_returns_null_when_not_found_contract()
  {
    // 事前条件: 認証済みユーザー
    /** @var User $user */
    $user = User::factory()->create();

    // モックの設定
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $transactionRepository->shouldReceive('findById')
      ->withAnyArgs()
      ->once()
      ->andReturn(null);

    $service = new TransactionService($transactionRepository, $authService);

    // Act: 存在しない取引IDで取得
    $result = $service->getTransaction('nonexistent-id');

    // 事後条件の検証
    $this->assertNull($result);
  }

  /**
   * @test
   * getTransaction メソッドの認証必須契約テスト
   * 
   * 事前条件: 未認証ユーザー
   * 事後条件: 例外が発生する
   * 不変条件: 例外メッセージが正しい
   */
  public function test_get_transaction_requires_authentication_contract()
  {
    // 事前条件: 取引データ
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $seller->id]);
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id,
      'status' => 'active'
    ]);

    // モックの設定
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $transactionEntity = new TransactionEntity(
      $transaction->id,
      $transaction->item_id,
      $transaction->buyer_id,
      $transaction->seller_id,
      $transaction->status,
      null
    );

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andThrow(new \Exception('認証が必要です。'));

    $transactionRepository->shouldReceive('findById')
      ->withAnyArgs()
      ->once()
      ->andReturn($transactionEntity);

    $service = new TransactionService($transactionRepository, $authService);

    // Act & Assert: 例外が発生すること
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('認証が必要です。');
    $service->getTransaction($transaction->id);
  }

  /**
   * @test
   * getTransaction メソッドの権限チェック契約テスト
   * 
   * 事前条件: 認証済みユーザー、取引が存在するがユーザーが取引に関与していない
   * 事後条件: Unauthorized例外が発生する
   * 不変条件: 例外メッセージが正しい
   */
  public function test_get_transaction_throws_exception_when_unauthorized_contract()
  {
    // 事前条件: 認証済みユーザー（取引に関与していない）
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    /** @var User $otherUser */
    $otherUser = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $seller->id]);
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id,
      'status' => 'active'
    ]);

    // モックの設定
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $transactionEntity = new TransactionEntity(
      $transaction->id,
      $transaction->item_id,
      $transaction->buyer_id,
      $transaction->seller_id,
      $transaction->status,
      null
    );

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andReturn($otherUser->id);

    $transactionRepository->shouldReceive('findById')
      ->withAnyArgs()
      ->once()
      ->andReturn($transactionEntity);

    $service = new TransactionService($transactionRepository, $authService);

    // Act & Assert: Unauthorized例外が発生すること
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Unauthorized');
    $service->getTransaction($transaction->id);
  }

  /**
   * @test
   * getUserTransactions メソッドの正常取得契約テスト
   * 
   * 事前条件: 認証済みユーザー
   * 事後条件: ユーザーが参加している取引一覧が返される
   * 不変条件: 返される配列の各要素がTransactionEntityであること
   */
  public function test_get_user_transactions_returns_array_with_correct_contract()
  {
    // 事前条件: 認証済みユーザーと取引データ
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $seller->id]);
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id,
      'status' => 'active'
    ]);

    // モックの設定
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $transactionEntity = new TransactionEntity(
      $transaction->id,
      $transaction->item_id,
      $transaction->buyer_id,
      $transaction->seller_id,
      $transaction->status,
      null
    );

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andReturn($buyer->id);

    $transactionRepository->shouldReceive('findByUserId')
      ->withAnyArgs()
      ->once()
      ->andReturn([$transactionEntity]);

    $service = new TransactionService($transactionRepository, $authService);

    // Act: ユーザーの取引一覧取得
    $result = $service->getUserTransactions();

    // 事後条件の検証
    $this->assertIsArray($result);
    $this->assertCount(1, $result);

    // 不変条件: 各要素がTransactionEntityであること
    $this->assertInstanceOf(TransactionEntity::class, $result[0]);
    $this->assertEquals($transaction->id, $result[0]->getId());
  }

  /**
   * @test
   * getUserTransactions メソッドの認証必須契約テスト
   * 
   * 事前条件: 未認証ユーザー
   * 事後条件: 例外が発生する
   * 不変条件: 例外メッセージが正しい
   */
  public function test_get_user_transactions_requires_authentication_contract()
  {
    // モックの設定
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andThrow(new \Exception('認証が必要です。'));

    $service = new TransactionService($transactionRepository, $authService);

    // Act & Assert: 例外が発生すること
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('認証が必要です。');
    $service->getUserTransactions();
  }

  /**
   * @test
   * completeTransaction メソッドの正常完了契約テスト
   * 
   * 事前条件: 認証済みユーザー（購入者）、取引が存在し、アクティブな状態
   * 事後条件: 取引が完了状態になり、completed_atが設定され、メールが送信される
   * 不変条件: 取引のステータスが'completed'になること
   */
  public function test_complete_transaction_completes_with_correct_contract()
  {
    // 事前条件: 認証済みユーザー（購入者）と取引データ
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $seller->id]);
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id,
      'status' => 'active'
    ]);

    // モックの設定
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $transactionEntity = new TransactionEntity(
      $transaction->id,
      $transaction->item_id,
      $transaction->buyer_id,
      $transaction->seller_id,
      $transaction->status,
      null
    );

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andReturn($buyer->id);

    $transactionRepository->shouldReceive('findById')
      ->withAnyArgs()
      ->once()
      ->andReturn($transactionEntity);

    $transactionRepository->shouldReceive('update')
      ->once()
      ->with(Mockery::type(TransactionEntity::class));

    $service = new TransactionService($transactionRepository, $authService);

    // Act: 取引完了
    $result = $service->completeTransaction($transaction->id);

    // 事後条件の検証
    $this->assertInstanceOf(TransactionEntity::class, $result);
    $this->assertEquals('completed', $result->getStatus());
    $this->assertNotNull($result->getCompletedAt());

    // 不変条件: メールが送信されること
    Mail::assertSent(\App\Mail\TransactionCompletedMail::class);
  }

  /**
   * @test
   * completeTransaction メソッドの取引不存在契約テスト
   * 
   * 事前条件: 認証済みユーザー、存在しない取引ID
   * 事後条件: Transaction not found例外が発生する
   * 不変条件: 例外メッセージが正しい
   */
  public function test_complete_transaction_throws_exception_when_not_found_contract()
  {
    // 事前条件: 認証済みユーザー
    /** @var User $user */
    $user = User::factory()->create();

    // モックの設定
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andReturn($user->id);

    $transactionRepository->shouldReceive('findById')
      ->withAnyArgs()
      ->once()
      ->andReturn(null);

    $service = new TransactionService($transactionRepository, $authService);

    // Act & Assert: 例外が発生すること
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Transaction not found');
    $service->completeTransaction('nonexistent-id');
  }

  /**
   * @test
   * completeTransaction メソッドの権限チェック契約テスト
   * 
   * 事前条件: 認証済みユーザー（出品者）、取引が存在する
   * 事後条件: Only buyer can complete the transaction例外が発生する
   * 不変条件: 例外メッセージが正しい
   */
  public function test_complete_transaction_throws_exception_when_not_buyer_contract()
  {
    // 事前条件: 認証済みユーザー（出品者）と取引データ
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $seller->id]);
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id,
      'status' => 'active'
    ]);

    // モックの設定
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $transactionEntity = new TransactionEntity(
      $transaction->id,
      $transaction->item_id,
      $transaction->buyer_id,
      $transaction->seller_id,
      $transaction->status,
      null
    );

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andReturn($seller->id);

    $transactionRepository->shouldReceive('findById')
      ->withAnyArgs()
      ->once()
      ->andReturn($transactionEntity);

    $service = new TransactionService($transactionRepository, $authService);

    // Act & Assert: 例外が発生すること
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Only buyer can complete the transaction');
    $service->completeTransaction($transaction->id);
  }

  /**
   * @test
   * completeTransaction メソッドの既完了チェック契約テスト
   * 
   * 事前条件: 認証済みユーザー（購入者）、既に完了している取引
   * 事後条件: Transaction is already completed例外が発生する
   * 不変条件: 例外メッセージが正しい
   */
  public function test_complete_transaction_throws_exception_when_already_completed_contract()
  {
    // 事前条件: 認証済みユーザー（購入者）と完了済み取引
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $seller->id]);
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id,
      'status' => 'completed',
      'completed_at' => now()
    ]);

    // モックの設定
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $transactionEntity = new TransactionEntity(
      $transaction->id,
      $transaction->item_id,
      $transaction->buyer_id,
      $transaction->seller_id,
      $transaction->status,
      new \DateTime($transaction->completed_at->format('Y-m-d H:i:s'))
    );

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andReturn($buyer->id);

    $transactionRepository->shouldReceive('findById')
      ->withAnyArgs()
      ->once()
      ->andReturn($transactionEntity);

    $service = new TransactionService($transactionRepository, $authService);

    // Act & Assert: 例外が発生すること
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Transaction is already completed');
    $service->completeTransaction($transaction->id);
  }

  /**
   * @test
   * hasActiveTransaction メソッドの正常チェック契約テスト
   * 
   * 事前条件: 有効なアイテムID
   * 事後条件: アクティブな取引がある場合はtrue、ない場合はfalse
   * 不変条件: 戻り値がbooleanであること
   */
  public function test_has_active_transaction_returns_boolean_with_correct_contract()
  {
    // 事前条件: アイテムデータ
    /** @var User $seller */
    $seller = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $seller->id]);

    // モックの設定
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    // アクティブな取引がある場合
    $transactionEntity = new TransactionEntity(
      'test-transaction-id',
      $item->id,
      'test-buyer-id',
      $seller->id,
      'active',
      null
    );

    $transactionRepository->shouldReceive('findActiveByItemId')
      ->withAnyArgs()
      ->once()
      ->andReturn($transactionEntity);

    $service = new TransactionService($transactionRepository, $authService);

    // Act: アクティブな取引チェック
    $result = $service->hasActiveTransaction($item->id);

    // 事後条件の検証
    $this->assertIsBool($result);
    $this->assertTrue($result);

    // アクティブな取引がない場合 - 新しいモックインスタンスを作成
    $transactionRepository2 = Mockery::mock(TransactionRepositoryInterface::class);
    $transactionRepository2->shouldReceive('findActiveByItemId')
      ->withAnyArgs()
      ->once()
      ->andReturn(null);

    $service2 = new TransactionService($transactionRepository2, $authService);
    $result = $service2->hasActiveTransaction('other-item-id');

    // 事後条件の検証
    $this->assertIsBool($result);
    $this->assertFalse($result);
  }

  /**
   * @test
   * TransactionService クラスの不変条件テスト
   */
  public function test_transaction_service_invariants()
  {
    $transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $service = new TransactionService($transactionRepository, $authService);

    // 不変条件1: 必要な依存関係が正しく注入されていること
    $reflection = new \ReflectionClass($service);
    $this->assertTrue($reflection->hasProperty('transactionRepository'));
    $this->assertTrue($reflection->hasProperty('authService'));

    // 不変条件2: 必要なメソッドが存在すること
    $expectedMethods = ['getTransaction', 'getUserTransactions', 'completeTransaction', 'hasActiveTransaction'];
    foreach ($expectedMethods as $method) {
      $this->assertTrue(
        method_exists($service, $method),
        "必要なメソッド '{$method}' が存在しません"
      );
    }
  }
}
