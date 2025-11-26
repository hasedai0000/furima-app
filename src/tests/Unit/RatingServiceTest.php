<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Application\Services\RatingService;
use App\Application\Services\AuthenticationService;
use App\Domain\Transaction\Entities\Rating as RatingEntity;
use App\Domain\Transaction\Repositories\RatingRepositoryInterface;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Rating;
use Mockery;

/**
 * RatingServiceのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 */
class RatingServiceTest extends TestCase
{
  protected function tearDown(): void
  {
    parent::tearDown();
    Mockery::close();
  }

  /**
   * @test
   * createRating メソッドの正常作成契約テスト
   * 
   * 事前条件: 認証済みユーザー、評価値が1-5の範囲
   * 事後条件: 評価エンティティが作成され、保存される
   * 不変条件: 評価エンティティの整合性が保たれる
   */
  public function test_create_rating_creates_entity_with_correct_contract()
  {
    // 事前条件: 認証済みユーザーと取引データ
    /** @var User $rater */
    $rater = User::factory()->create();
    /** @var User $rated */
    $rated = User::factory()->create();
    $transaction = Transaction::factory()->create();

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andReturn($rater->id);

    $ratingRepository->shouldReceive('save')
      ->once()
      ->with(Mockery::type(RatingEntity::class));

    $service = new RatingService($ratingRepository, $authService);

    // Act: 評価作成
    $result = $service->createRating($transaction->id, $rated->id, 5, '素晴らしい取引でした');

    // 事後条件の検証
    $this->assertInstanceOf(RatingEntity::class, $result);
    $this->assertEquals($transaction->id, $result->getTransactionId());
    $this->assertEquals($rater->id, $result->getRaterId());
    $this->assertEquals($rated->id, $result->getRatedId());
    $this->assertEquals(5, $result->getRating());
    $this->assertEquals('素晴らしい取引でした', $result->getComment());

    // 不変条件: 評価エンティティの整合性
    $this->assertNotEmpty($result->getId());
    $this->assertNotNull($result->getCreatedAt());
  }

  /**
   * @test
   * createRating メソッドの評価範囲チェック契約テスト（下限）
   * 
   * 事前条件: 認証済みユーザー、評価値が0以下
   * 事後条件: Rating must be between 1 and 5例外が発生する
   * 不変条件: 例外メッセージが正しい
   */
  public function test_create_rating_throws_exception_when_rating_below_minimum_contract()
  {
    // 事前条件: 認証済みユーザー
    /** @var User $rater */
    $rater = User::factory()->create();
    /** @var User $rated */
    $rated = User::factory()->create();
    $transaction = Transaction::factory()->create();

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andReturn($rater->id);

    $service = new RatingService($ratingRepository, $authService);

    // Act & Assert: 例外が発生すること
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Rating must be between 1 and 5');
    $service->createRating($transaction->id, $rated->id, 0);
  }

  /**
   * @test
   * createRating メソッドの評価範囲チェック契約テスト（上限）
   * 
   * 事前条件: 認証済みユーザー、評価値が6以上
   * 事後条件: Rating must be between 1 and 5例外が発生する
   * 不変条件: 例外メッセージが正しい
   */
  public function test_create_rating_throws_exception_when_rating_above_maximum_contract()
  {
    // 事前条件: 認証済みユーザー
    /** @var User $rater */
    $rater = User::factory()->create();
    /** @var User $rated */
    $rated = User::factory()->create();
    $transaction = Transaction::factory()->create();

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andReturn($rater->id);

    $service = new RatingService($ratingRepository, $authService);

    // Act & Assert: 例外が発生すること
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Rating must be between 1 and 5');
    $service->createRating($transaction->id, $rated->id, 6);
  }

  /**
   * @test
   * createRating メソッドの認証必須契約テスト
   * 
   * 事前条件: 未認証ユーザー
   * 事後条件: 例外が発生する
   * 不変条件: 例外メッセージが正しい
   */
  public function test_create_rating_requires_authentication_contract()
  {
    // 事前条件: 取引データ
    /** @var User $rated */
    $rated = User::factory()->create();
    $transaction = Transaction::factory()->create();

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andThrow(new \Exception('認証が必要です。'));

    $service = new RatingService($ratingRepository, $authService);

    // Act & Assert: 例外が発生すること
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('認証が必要です。');
    $service->createRating($transaction->id, $rated->id, 5);
  }

  /**
   * @test
   * createRating メソッドのコメントなし作成契約テスト
   * 
   * 事前条件: 認証済みユーザー、評価値が1-5の範囲、コメントなし
   * 事後条件: 評価エンティティが作成され、コメントがnullになる
   * 不変条件: 評価エンティティの整合性が保たれる
   */
  public function test_create_rating_without_comment_creates_entity_with_correct_contract()
  {
    // 事前条件: 認証済みユーザーと取引データ
    /** @var User $rater */
    $rater = User::factory()->create();
    /** @var User $rated */
    $rated = User::factory()->create();
    $transaction = Transaction::factory()->create();

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $authService->shouldReceive('requireAuthentication')
      ->once()
      ->andReturn($rater->id);

    $ratingRepository->shouldReceive('save')
      ->once()
      ->with(Mockery::type(RatingEntity::class));

    $service = new RatingService($ratingRepository, $authService);

    // Act: 評価作成（コメントなし）
    $result = $service->createRating($transaction->id, $rated->id, 4);

    // 事後条件の検証
    $this->assertInstanceOf(RatingEntity::class, $result);
    $this->assertNull($result->getComment());
  }

  /**
   * @test
   * getRatingsByTransactionId メソッドの正常取得契約テスト
   * 
   * 事前条件: 有効な取引ID
   * 事後条件: 取引に紐づく評価一覧が返される
   * 不変条件: 返される配列の各要素がRatingEntityであること
   */
  public function test_get_ratings_by_transaction_id_returns_array_with_correct_contract()
  {
    // 事前条件: 取引データと評価データ
    /** @var User $rater */
    $rater = User::factory()->create();
    /** @var User $rated */
    $rated = User::factory()->create();
    $transaction = Transaction::factory()->create();
    $rating = Rating::factory()->create([
      'transaction_id' => $transaction->id,
      'rater_id' => $rater->id,
      'rated_id' => $rated->id,
      'rating' => 5
    ]);

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $ratingEntity = new RatingEntity(
      $rating->id,
      $rating->transaction_id,
      $rating->rater_id,
      $rating->rated_id,
      $rating->rating,
      $rating->comment,
      new \DateTime($rating->created_at->format('Y-m-d H:i:s'))
    );

    $ratingRepository->shouldReceive('findByTransactionId')
      ->withAnyArgs()
      ->once()
      ->andReturn([$ratingEntity]);

    $service = new RatingService($ratingRepository, $authService);

    // Act: 取引IDに紐づく評価取得
    $result = $service->getRatingsByTransactionId($transaction->id);

    // 事後条件の検証
    $this->assertIsArray($result);
    $this->assertCount(1, $result);

    // 不変条件: 各要素がRatingEntityであること
    $this->assertInstanceOf(RatingEntity::class, $result[0]);
    $this->assertEquals($rating->id, $result[0]->getId());
    $this->assertEquals($transaction->id, $result[0]->getTransactionId());
  }

  /**
   * @test
   * getRatingsByTransactionId メソッドの空配列返却契約テスト
   * 
   * 事前条件: 有効な取引ID、評価が存在しない
   * 事後条件: 空配列が返される
   * 不変条件: 返される値が配列であること
   */
  public function test_get_ratings_by_transaction_id_returns_empty_array_when_no_ratings_contract()
  {
    // 事前条件: 取引データ（評価なし）
    $transaction = Transaction::factory()->create();

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $ratingRepository->shouldReceive('findByTransactionId')
      ->withAnyArgs()
      ->once()
      ->andReturn([]);

    $service = new RatingService($ratingRepository, $authService);

    // Act: 取引IDに紐づく評価取得
    $result = $service->getRatingsByTransactionId($transaction->id);

    // 事後条件の検証
    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }

  /**
   * @test
   * getAverageRating メソッドの正常計算契約テスト
   * 
   * 事前条件: 有効なユーザーID、評価が存在する
   * 事後条件: ユーザーの評価平均が返される（四捨五入）
   * 不変条件: 返される値が0-5の範囲のfloatであること
   */
  public function test_get_average_rating_returns_float_with_correct_contract()
  {
    // 事前条件: ユーザーと評価データ
    /** @var User $rated */
    $rated = User::factory()->create();
    /** @var User $rater1 */
    $rater1 = User::factory()->create();
    /** @var User $rater2 */
    $rater2 = User::factory()->create();
    $transaction1 = Transaction::factory()->create();
    $transaction2 = Transaction::factory()->create();

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $ratingEntity1 = new RatingEntity(
      'rating-id-1',
      $transaction1->id,
      $rater1->id,
      $rated->id,
      4,
      null
    );

    $ratingEntity2 = new RatingEntity(
      'rating-id-2',
      $transaction2->id,
      $rater2->id,
      $rated->id,
      5,
      null
    );

    $ratingRepository->shouldReceive('findByRatedUserId')
      ->withAnyArgs()
      ->once()
      ->andReturn([$ratingEntity1, $ratingEntity2]);

    $service = new RatingService($ratingRepository, $authService);

    // Act: 評価平均取得
    $result = $service->getAverageRating($rated->id);

    // 事後条件の検証
    $this->assertIsFloat($result);
    // (4 + 5) / 2 = 4.5 → 四捨五入で5
    $this->assertEquals(5.0, $result);

    // 不変条件: 値が0-5の範囲であること
    $this->assertGreaterThanOrEqual(0, $result);
    $this->assertLessThanOrEqual(5, $result);
  }

  /**
   * @test
   * getAverageRating メソッドの評価なし契約テスト
   * 
   * 事前条件: 有効なユーザーID、評価が存在しない
   * 事後条件: nullが返される
   * 不変条件: 返される値がnullであること
   */
  public function test_get_average_rating_returns_null_when_no_ratings_contract()
  {
    // 事前条件: ユーザー（評価なし）
    /** @var User $user */
    $user = User::factory()->create();

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $ratingRepository->shouldReceive('findByRatedUserId')
      ->withAnyArgs()
      ->once()
      ->andReturn([]);

    $service = new RatingService($ratingRepository, $authService);

    // Act: 評価平均取得
    $result = $service->getAverageRating($user->id);

    // 事後条件の検証
    $this->assertNull($result);
  }

  /**
   * @test
   * getAverageRating メソッドの四捨五入契約テスト
   * 
   * 事前条件: 有効なユーザーID、評価が存在し、平均が小数になる
   * 事後条件: 評価平均が四捨五入されて返される
   * 不変条件: 返される値が整数値（float型）であること
   */
  public function test_get_average_rating_rounds_correctly_contract()
  {
    // 事前条件: ユーザーと評価データ
    /** @var User $rated */
    $rated = User::factory()->create();
    /** @var User $rater1 */
    $rater1 = User::factory()->create();
    /** @var User $rater2 */
    $rater2 = User::factory()->create();
    /** @var User $rater3 */
    $rater3 = User::factory()->create();
    $transaction1 = Transaction::factory()->create();
    $transaction2 = Transaction::factory()->create();
    $transaction3 = Transaction::factory()->create();

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $ratingEntity1 = new RatingEntity('rating-id-1', $transaction1->id, $rater1->id, $rated->id, 3, null);
    $ratingEntity2 = new RatingEntity('rating-id-2', $transaction2->id, $rater2->id, $rated->id, 4, null);
    $ratingEntity3 = new RatingEntity('rating-id-3', $transaction3->id, $rater3->id, $rated->id, 4, null);

    $ratingRepository->shouldReceive('findByRatedUserId')
      ->withAnyArgs()
      ->once()
      ->andReturn([$ratingEntity1, $ratingEntity2, $ratingEntity3]);

    $service = new RatingService($ratingRepository, $authService);

    // Act: 評価平均取得
    $result = $service->getAverageRating($rated->id);

    // 事後条件の検証
    // (3 + 4 + 4) / 3 = 3.666... → 四捨五入で4
    $this->assertEquals(4.0, $result);
  }

  /**
   * @test
   * hasRated メソッドの正常チェック契約テスト（評価済み）
   * 
   * 事前条件: 有効な取引IDとユーザーID、ユーザーが評価済み
   * 事後条件: trueが返される
   * 不変条件: 戻り値がbooleanであること
   */
  public function test_has_rated_returns_true_when_rated_contract()
  {
    // 事前条件: ユーザーと取引データ
    /** @var User $rater */
    $rater = User::factory()->create();
    /** @var User $rated */
    $rated = User::factory()->create();
    $transaction = Transaction::factory()->create();

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $ratingEntity = new RatingEntity(
      'rating-id',
      $transaction->id,
      $rater->id,
      $rated->id,
      5,
      null
    );

    $ratingRepository->shouldReceive('findByTransactionId')
      ->withAnyArgs()
      ->once()
      ->andReturn([$ratingEntity]);

    $service = new RatingService($ratingRepository, $authService);

    // Act: 評価済みチェック
    $result = $service->hasRated($transaction->id, $rater->id);

    // 事後条件の検証
    $this->assertIsBool($result);
    $this->assertTrue($result);
  }

  /**
   * @test
   * hasRated メソッドの正常チェック契約テスト（未評価）
   * 
   * 事前条件: 有効な取引IDとユーザーID、ユーザーが未評価
   * 事後条件: falseが返される
   * 不変条件: 戻り値がbooleanであること
   */
  public function test_has_rated_returns_false_when_not_rated_contract()
  {
    // 事前条件: ユーザーと取引データ
    /** @var User $rater */
    $rater = User::factory()->create();
    /** @var User $otherRater */
    $otherRater = User::factory()->create();
    /** @var User $rated */
    $rated = User::factory()->create();
    $transaction = Transaction::factory()->create();

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $ratingEntity = new RatingEntity(
      'rating-id',
      $transaction->id,
      $otherRater->id,
      $rated->id,
      5,
      null
    );

    $ratingRepository->shouldReceive('findByTransactionId')
      ->withAnyArgs()
      ->once()
      ->andReturn([$ratingEntity]);

    $service = new RatingService($ratingRepository, $authService);

    // Act: 評価済みチェック
    $result = $service->hasRated($transaction->id, $rater->id);

    // 事後条件の検証
    $this->assertIsBool($result);
    $this->assertFalse($result);
  }

  /**
   * @test
   * isRatingCompleted メソッドの正常チェック契約テスト（完了）
   * 
   * 事前条件: 有効な取引ID、購入者ID、出品者ID、両方が評価済み
   * 事後条件: trueが返される
   * 不変条件: 戻り値がbooleanであること
   */
  public function test_is_rating_completed_returns_true_when_both_rated_contract()
  {
    // 事前条件: ユーザーと取引データ
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    $transaction = Transaction::factory()->create([
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id
    ]);

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $ratingEntity1 = new RatingEntity(
      'rating-id-1',
      $transaction->id,
      $buyer->id,
      $seller->id,
      5,
      null
    );

    $ratingEntity2 = new RatingEntity(
      'rating-id-2',
      $transaction->id,
      $seller->id,
      $buyer->id,
      4,
      null
    );

    $ratingRepository->shouldReceive('findByTransactionId')
      ->withAnyArgs()
      ->once()
      ->andReturn([$ratingEntity1, $ratingEntity2]);

    $service = new RatingService($ratingRepository, $authService);

    // Act: 評価完了チェック
    $result = $service->isRatingCompleted($transaction->id, $buyer->id, $seller->id);

    // 事後条件の検証
    $this->assertIsBool($result);
    $this->assertTrue($result);
  }

  /**
   * @test
   * isRatingCompleted メソッドの正常チェック契約テスト（未完了）
   * 
   * 事前条件: 有効な取引ID、購入者ID、出品者ID、片方のみ評価済み
   * 事後条件: falseが返される
   * 不変条件: 戻り値がbooleanであること
   */
  public function test_is_rating_completed_returns_false_when_not_both_rated_contract()
  {
    // 事前条件: ユーザーと取引データ
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    $transaction = Transaction::factory()->create([
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id
    ]);

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    // 購入者のみ評価済み
    $ratingEntity = new RatingEntity(
      'rating-id',
      $transaction->id,
      $buyer->id,
      $seller->id,
      5,
      null
    );

    $ratingRepository->shouldReceive('findByTransactionId')
      ->withAnyArgs()
      ->once()
      ->andReturn([$ratingEntity]);

    $service = new RatingService($ratingRepository, $authService);

    // Act: 評価完了チェック
    $result = $service->isRatingCompleted($transaction->id, $buyer->id, $seller->id);

    // 事後条件の検証
    $this->assertIsBool($result);
    $this->assertFalse($result);
  }

  /**
   * @test
   * isRatingCompleted メソッドの評価なし契約テスト
   * 
   * 事前条件: 有効な取引ID、購入者ID、出品者ID、評価が存在しない
   * 事後条件: falseが返される
   * 不変条件: 戻り値がbooleanであること
   */
  public function test_is_rating_completed_returns_false_when_no_ratings_contract()
  {
    // 事前条件: ユーザーと取引データ（評価なし）
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    $transaction = Transaction::factory()->create([
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id
    ]);

    // モックの設定
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $ratingRepository->shouldReceive('findByTransactionId')
      ->withAnyArgs()
      ->once()
      ->andReturn([]);

    $service = new RatingService($ratingRepository, $authService);

    // Act: 評価完了チェック
    $result = $service->isRatingCompleted($transaction->id, $buyer->id, $seller->id);

    // 事後条件の検証
    $this->assertIsBool($result);
    $this->assertFalse($result);
  }

  /**
   * @test
   * RatingService クラスの不変条件テスト
   */
  public function test_rating_service_invariants()
  {
    $ratingRepository = Mockery::mock(RatingRepositoryInterface::class);
    $authService = Mockery::mock(AuthenticationService::class);

    $service = new RatingService($ratingRepository, $authService);

    // 不変条件1: 必要な依存関係が正しく注入されていること
    $reflection = new \ReflectionClass($service);
    $this->assertTrue($reflection->hasProperty('ratingRepository'));
    $this->assertTrue($reflection->hasProperty('authService'));

    // 不変条件2: 必要なメソッドが存在すること
    $expectedMethods = [
      'createRating',
      'getRatingsByTransactionId',
      'getAverageRating',
      'hasRated',
      'isRatingCompleted'
    ];
    foreach ($expectedMethods as $method) {
      $this->assertTrue(
        method_exists($service, $method),
        "必要なメソッド '{$method}' が存在しません"
      );
    }
  }
}
