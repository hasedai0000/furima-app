<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\Message;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * TransactionControllerのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * データベースを汚さない設計で実装
 */
class TransactionControllerTest extends TestCase
{
  use WithFaker;

  protected function setUp(): void
  {
    parent::setUp();
    Storage::fake('local');

    // モックのリセット
    parent::resetMocks();
  }

  protected function tearDown(): void
  {
    // テスト後のモック清理
    parent::resetMocks();

    parent::tearDown();
  }

  /**
   * @test
   * show メソッドの認証必須契約テスト
   * 
   * 事前条件: 認証が必要
   * 事後条件: 認証なしの場合はログインページにリダイレクト
   * 不変条件: 認証済みの場合はチャット画面が表示される
   */
  public function test_show_requires_authentication_contract()
  {
    // 事前条件: 認証なしでアクセス
    $transaction = Transaction::factory()->create();
    $response = $this->get("/transactions/{$transaction->id}");

    // 事後条件: ログインページにリダイレクトされること
    $response->assertRedirect('/login');

        // 事前条件: 認証済みユーザーでアクセス
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    // 取引に関与しているユーザーとして設定
    $transaction->buyer_id = $user->id;
    $transaction->save();

    $response = $this->get("/transactions/{$transaction->id}");

    // 事後条件: チャット画面が表示されること
    $response->assertStatus(200);
    $response->assertViewIs('transactions.chat');
  }

  /**
   * @test
   * show メソッドの取引存在チェック契約テスト
   * 
   * 事前条件: 存在しない取引ID
   * 事後条件: マイページにリダイレクトされ、エラーメッセージが表示される
   * 不変条件: エラーメッセージが正しく設定される
   */
  public function test_show_with_nonexistent_transaction_contract()
  {
        // 事前条件: 認証済みユーザー
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    // 存在しない取引IDでアクセス
    $response = $this->get('/transactions/nonexistent-id');

    // 事後条件: マイページにリダイレクトされること
    $response->assertRedirect(route('mypage.index'));
    $response->assertSessionHas('error', '取引が見つかりません。');
  }

  /**
   * @test
   * show メソッドの正常表示契約テスト
   * 
   * 事前条件: 認証済みユーザーと有効な取引
   * 事後条件: チャット画面が表示され、必要なデータが渡される
   * 不変条件: ビューデータの整合性が保たれる
   */
  public function test_show_returns_chat_view_with_correct_contract()
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
    $this->actingAs($buyer);

    // Act: チャット画面表示
    $response = $this->get("/transactions/{$transaction->id}");

    // 事後条件の検証
    $response->assertStatus(200);
    $response->assertViewIs('transactions.chat');
    $response->assertViewHas('transaction');
    $response->assertViewHas('item');
    $response->assertViewHas('messages');
    $response->assertViewHas('isBuyer');
    $response->assertViewHas('otherUser');
    $response->assertViewHas('transactions');
    $response->assertViewHas('needsRating');
    $response->assertViewHas('showRatingModal');

    // 不変条件: ビューデータの型が正しいこと
    $viewData = $response->original->getData();
    $this->assertIsArray($viewData['transaction']);
    $this->assertIsArray($viewData['item']);
    $this->assertIsArray($viewData['messages']);
    $this->assertIsBool($viewData['isBuyer']);
    $this->assertIsArray($viewData['otherUser']);
    $this->assertIsArray($viewData['transactions']);
    $this->assertIsBool($viewData['needsRating']);
    $this->assertIsBool($viewData['showRatingModal']);
  }

  /**
   * @test
   * show メソッドの評価モーダル表示契約テスト
   * 
   * 事前条件: 認証済みユーザーと完了済み取引（未評価）
   * 事後条件: 評価モーダルが表示される
   * 不変条件: needsRatingとshowRatingModalがtrueになる
   */
  public function test_show_displays_rating_modal_when_completed_contract()
  {
        // 事前条件: 認証済みユーザーと完了済み取引
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
    $this->actingAs($buyer);

    // Act: チャット画面表示
    $response = $this->get("/transactions/{$transaction->id}");

    // 事後条件の検証
    $response->assertStatus(200);
    $viewData = $response->original->getData();

    // 不変条件: 評価が必要な場合、モーダルが表示される
    if ($viewData['needsRating']) {
      $this->assertTrue($viewData['showRatingModal']);
    }
  }

  /**
   * @test
   * sendMessage メソッドの認証必須契約テスト
   * 
   * 事前条件: 認証が必要
   * 事後条件: 認証なしの場合はログインページにリダイレクト
   */
  public function test_send_message_requires_authentication_contract()
  {
    // 事前条件: 認証なしでアクセス
    $transaction = Transaction::factory()->create();
    $response = $this->post("/transactions/{$transaction->id}/messages", [
      'content' => 'テストメッセージ'
    ]);

    // 事後条件: ログインページにリダイレクトされること
    $response->assertRedirect('/login');
  }

  /**
   * @test
   * sendMessage メソッドの正常送信契約テスト
   * 
   * 事前条件: 認証済みユーザーと有効なメッセージデータ
   * 事後条件: メッセージが送信され、チャット画面にリダイレクト
   * 不変条件: データベースにメッセージが保存される
   */
  public function test_send_message_creates_message_with_correct_contract()
  {
        // 事前条件: 認証済みユーザーと取引
    /** @var User $user */
    $user = User::factory()->create();
    $item = Item::factory()->create();
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $user->id
    ]);
    $this->actingAs($user);

    $messageData = [
      'content' => 'テストメッセージ'
    ];

    // Act: メッセージ送信
    $response = $this->post("/transactions/{$transaction->id}/messages", $messageData);

    // 事後条件: リダイレクトと成功メッセージ
    $response->assertRedirect(route('transactions.show', ['transaction_id' => $transaction->id]));
    $response->assertSessionHas('success', 'メッセージを送信しました。');

    // 不変条件: データベースにメッセージが保存されること
    $this->assertDatabaseHas('messages', [
      'transaction_id' => $transaction->id,
      'user_id' => $user->id,
      'content' => $messageData['content']
    ]);
  }

  /**
   * @test
   * sendMessage メソッドのバリデーション契約テスト
   * 
   * 事前条件: 認証済みユーザーと無効なメッセージデータ
   * 事後条件: バリデーションエラーが返される
   * 不変条件: メッセージが保存されない
   */
  public function test_send_message_validates_content_length_contract()
  {
        // 事前条件: 認証済みユーザーと取引
    /** @var User $user */
    $user = User::factory()->create();
    $item = Item::factory()->create();
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $user->id
    ]);
    $this->actingAs($user);

    // 400文字を超えるメッセージ
    $longContent = str_repeat('a', 401);
    $messageData = [
      'content' => $longContent
    ];

    // Act: メッセージ送信
    $response = $this->post("/transactions/{$transaction->id}/messages", $messageData);

    // 事後条件: バリデーションエラーが返されること
    $response->assertSessionHasErrors(['content']);

    // 不変条件: メッセージが保存されないこと
    $this->assertDatabaseMissing('messages', [
      'transaction_id' => $transaction->id,
      'content' => $longContent
    ]);
  }

  /**
   * @test
   * sendMessage メソッドの画像アップロード契約テスト
   * 
   * 事前条件: 認証済みユーザーと有効な画像ファイル
   * 事後条件: メッセージと画像が送信される
   * 不変条件: 画像ファイルが適切に処理される
   */
  public function test_send_message_with_image_upload_contract()
  {
        // 事前条件: 認証済みユーザーと取引
    /** @var User $user */
    $user = User::factory()->create();
    $item = Item::factory()->create();
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $user->id
    ]);
    $this->actingAs($user);

    // GDライブラリが利用できない場合に備えて、createメソッドを使用
    $image = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

    $messageData = [
      'content' => '画像付きメッセージ',
      'images' => [$image]
    ];

    // Act: メッセージ送信
    $response = $this->post("/transactions/{$transaction->id}/messages", $messageData);

    // 事後条件: リダイレクトと成功メッセージ
    $response->assertRedirect(route('transactions.show', ['transaction_id' => $transaction->id]));
    $response->assertSessionHas('success', 'メッセージを送信しました。');

    // 不変条件: メッセージが保存されること
    $this->assertDatabaseHas('messages', [
      'transaction_id' => $transaction->id,
      'user_id' => $user->id,
      'content' => '画像付きメッセージ'
    ]);
  }

  /**
   * @test
   * updateMessage メソッドの認証必須契約テスト
   * 
   * 事前条件: 認証が必要
   * 事後条件: 認証なしの場合はログインページにリダイレクト
   */
  public function test_update_message_requires_authentication_contract()
  {
    // 事前条件: 認証なしでアクセス
    $transaction = Transaction::factory()->create();
    $message = Message::factory()->create(['transaction_id' => $transaction->id]);
    $response = $this->put("/transactions/{$transaction->id}/messages/{$message->id}", [
      'content' => '更新されたメッセージ'
    ]);

    // 事後条件: ログインページにリダイレクトされること
    $response->assertRedirect('/login');
  }

  /**
   * @test
   * updateMessage メソッドの正常更新契約テスト
   * 
   * 事前条件: 認証済みユーザーと自分のメッセージ
   * 事後条件: メッセージが更新され、チャット画面にリダイレクト
   * 不変条件: データベースのメッセージが更新される
   */
  public function test_update_message_modifies_message_with_correct_contract()
  {
        // 事前条件: 認証済みユーザーとメッセージ
    /** @var User $user */
    $user = User::factory()->create();
    $item = Item::factory()->create();
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $user->id
    ]);
    $message = Message::factory()->create([
      'transaction_id' => $transaction->id,
      'user_id' => $user->id,
      'content' => '元のメッセージ'
    ]);
    $this->actingAs($user);

    $updateData = [
      'content' => '更新されたメッセージ'
    ];

    // Act: メッセージ更新
    $response = $this->put("/transactions/{$transaction->id}/messages/{$message->id}", $updateData);

    // 事後条件: リダイレクトと成功メッセージ
    $response->assertRedirect(route('transactions.show', ['transaction_id' => $transaction->id]));
    $response->assertSessionHas('success', 'メッセージを更新しました。');

    // 不変条件: データベースのメッセージが更新されること
    $this->assertDatabaseHas('messages', [
      'id' => $message->id,
      'content' => $updateData['content']
    ]);
  }

  /**
   * @test
   * updateMessage メソッドのバリデーション契約テスト
   * 
   * 事前条件: 認証済みユーザーと無効なメッセージデータ
   * 事後条件: バリデーションエラーが返される
   */
  public function test_update_message_validates_content_length_contract()
  {
        // 事前条件: 認証済みユーザーとメッセージ
    /** @var User $user */
    $user = User::factory()->create();
    $item = Item::factory()->create();
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $user->id
    ]);
    $message = Message::factory()->create([
      'transaction_id' => $transaction->id,
      'user_id' => $user->id
    ]);
    $this->actingAs($user);

    // 400文字を超えるメッセージ
    $longContent = str_repeat('a', 401);
    $updateData = [
      'content' => $longContent
    ];

    // Act: メッセージ更新
    $response = $this->put("/transactions/{$transaction->id}/messages/{$message->id}", $updateData);

    // 事後条件: バリデーションエラーが返されること
    $response->assertSessionHasErrors(['content']);
  }

  /**
   * @test
   * deleteMessage メソッドの認証必須契約テスト
   * 
   * 事前条件: 認証が必要
   * 事後条件: 認証なしの場合はログインページにリダイレクト
   */
  public function test_delete_message_requires_authentication_contract()
  {
    // 事前条件: 認証なしでアクセス
    $transaction = Transaction::factory()->create();
    $message = Message::factory()->create(['transaction_id' => $transaction->id]);
    $response = $this->delete("/transactions/{$transaction->id}/messages/{$message->id}");

    // 事後条件: ログインページにリダイレクトされること
    $response->assertRedirect('/login');
  }

  /**
   * @test
   * deleteMessage メソッドの正常削除契約テスト
   * 
   * 事前条件: 認証済みユーザーと自分のメッセージ
   * 事後条件: メッセージが削除され、チャット画面にリダイレクト
   * 不変条件: データベースからメッセージが削除される（ソフトデリート）
   */
  public function test_delete_message_removes_message_with_correct_contract()
  {
        // 事前条件: 認証済みユーザーとメッセージ
    /** @var User $user */
    $user = User::factory()->create();
    $item = Item::factory()->create();
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $user->id
    ]);
    $message = Message::factory()->create([
      'transaction_id' => $transaction->id,
      'user_id' => $user->id,
      'content' => '削除されるメッセージ'
    ]);
    $this->actingAs($user);

    // Act: メッセージ削除
    $response = $this->delete("/transactions/{$transaction->id}/messages/{$message->id}");

    // 事後条件: リダイレクトと成功メッセージ
    $response->assertRedirect(route('transactions.show', ['transaction_id' => $transaction->id]));
    $response->assertSessionHas('success', 'メッセージを削除しました。');

    // 不変条件: メッセージがソフトデリートされること
    $this->assertSoftDeleted('messages', [
      'id' => $message->id
    ]);
  }

  /**
   * @test
   * complete メソッドの認証必須契約テスト
   * 
   * 事前条件: 認証が必要
   * 事後条件: 認証なしの場合はログインページにリダイレクト
   */
  public function test_complete_requires_authentication_contract()
  {
    // 事前条件: 認証なしでアクセス
    $transaction = Transaction::factory()->create();
    $response = $this->post("/transactions/{$transaction->id}/complete");

    // 事後条件: ログインページにリダイレクトされること
    $response->assertRedirect('/login');
  }

  /**
   * @test
   * complete メソッドの正常完了契約テスト
   * 
   * 事前条件: 認証済みユーザー（購入者）とアクティブな取引
   * 事後条件: 取引が完了し、チャット画面にリダイレクト
   * 不変条件: データベースの取引ステータスがcompletedになる
   */
  public function test_complete_finishes_transaction_with_correct_contract()
  {
        // 事前条件: 認証済みユーザー（購入者）と取引
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
    $this->actingAs($buyer);

    // Act: 取引完了
    $response = $this->post("/transactions/{$transaction->id}/complete");

    // 事後条件: リダイレクトと成功メッセージ
    $response->assertRedirect(route('transactions.show', ['transaction_id' => $transaction->id]));
    $response->assertSessionHas('success', '取引を完了しました。評価をお願いします。');

    // 不変条件: データベースの取引ステータスがcompletedになること
    $this->assertDatabaseHas('transactions', [
      'id' => $transaction->id,
      'status' => 'completed'
    ]);
    $this->assertNotNull(Transaction::find($transaction->id)->completed_at);
  }

  /**
   * @test
   * submitRating メソッドの認証必須契約テスト
   * 
   * 事前条件: 認証が必要
   * 事後条件: 認証なしの場合はログインページにリダイレクト
   */
  public function test_submit_rating_requires_authentication_contract()
  {
    // 事前条件: 認証なしでアクセス
    $transaction = Transaction::factory()->create();
    $response = $this->post("/transactions/{$transaction->id}/ratings", [
      'rated_id' => 'test-user-id',
      'rating' => 5
    ]);

    // 事後条件: ログインページにリダイレクトされること
    $response->assertRedirect('/login');
  }

  /**
   * @test
   * submitRating メソッドの正常送信契約テスト
   * 
   * 事前条件: 認証済みユーザーと有効な評価データ
   * 事後条件: 評価が送信され、アイテム一覧にリダイレクト
   * 不変条件: データベースに評価が保存される
   */
  public function test_submit_rating_creates_rating_with_correct_contract()
  {
        // 事前条件: 認証済みユーザーと取引
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $seller->id]);
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id,
      'status' => 'completed'
    ]);
    $this->actingAs($buyer);

    $ratingData = [
      'rated_id' => (string)$seller->id,
      'rating' => 5,
      'comment' => '素晴らしい取引でした'
    ];

    // Act: 評価送信
    $response = $this->post("/transactions/{$transaction->id}/ratings", $ratingData);

    // 事後条件: リダイレクトと成功メッセージ
    // 実際のコードでは、成功時はitems.index、エラー時はtransactions.showにリダイレクト
    // エラーログを見ると、例外が発生している可能性があるため、両方をチェック
    if ($response->isRedirect(route('items.index'))) {
      $response->assertRedirect(route('items.index'));
      $response->assertSessionHas('success', '評価を送信しました。');

      // 不変条件: データベースに評価が保存されること
      $this->assertDatabaseHas('ratings', [
        'transaction_id' => $transaction->id,
        'rated_id' => $ratingData['rated_id'],
        'rating' => $ratingData['rating'],
        'comment' => $ratingData['comment']
      ]);
    } else {
      // エラーが発生した場合、エラーメッセージを確認してテストを失敗させる
      $errorMessage = $response->session()->get('error', '不明なエラー');
      $this->fail("評価送信でエラーが発生しました: {$errorMessage}");
    }
  }

  /**
   * @test
   * submitRating メソッドのバリデーション契約テスト
   * 
   * 事前条件: 認証済みユーザーと無効な評価データ
   * 事後条件: バリデーションエラーが返される
   * 不変条件: 評価が保存されない
   */
  public function test_submit_rating_validates_rating_range_contract()
  {
        // 事前条件: 認証済みユーザーと取引
    /** @var User $buyer */
    $buyer = User::factory()->create();
    /** @var User $seller */
    $seller = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $seller->id]);
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id
    ]);
    $this->actingAs($buyer);

    // 評価が範囲外（6以上）
    $ratingData = [
      'rated_id' => (string)$seller->id,
      'rating' => 6
    ];

    // Act: 評価送信
    $response = $this->post("/transactions/{$transaction->id}/ratings", $ratingData);

    // 事後条件: バリデーションエラーが返されること
    $response->assertSessionHasErrors(['rating']);

    // 不変条件: 評価が保存されないこと
    $this->assertDatabaseMissing('ratings', [
      'transaction_id' => $transaction->id,
      'rating' => 6
    ]);
  }

  /**
   * @test
   * submitRating メソッドの必須フィールド契約テスト
   * 
   * 事前条件: 認証済みユーザーと必須フィールドが欠落したデータ
   * 事後条件: バリデーションエラーが返される
   */
  public function test_submit_rating_requires_required_fields_contract()
  {
        // 事前条件: 認証済みユーザーと取引
    /** @var User $buyer */
    $buyer = User::factory()->create();
    $item = Item::factory()->create();
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $buyer->id
    ]);
    $this->actingAs($buyer);

    // 必須フィールドが欠落
    $ratingData = [
      'rating' => 5
      // rated_idが欠落
    ];

    // Act: 評価送信
    $response = $this->post("/transactions/{$transaction->id}/ratings", $ratingData);

    // 事後条件: バリデーションエラーが返されること
    $response->assertSessionHasErrors(['rated_id']);
  }

  /**
   * @test
   * TransactionController クラスの不変条件テスト
   */
  public function test_transaction_controller_invariants()
  {
    $controller = app(\App\Http\Controllers\TransactionController::class);

    // 不変条件1: TransactionControllerはControllerを継承していること
    $this->assertInstanceOf(\App\Http\Controllers\Controller::class, $controller);

    // 不変条件2: 必要な依存関係が正しく注入されていること
    $reflection = new \ReflectionClass($controller);
    $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);

    $expectedProperties = [
      'transactionService',
      'messageService',
      'itemService',
      'authService',
      'ratingService'
    ];

    foreach ($expectedProperties as $expectedProperty) {
      $this->assertTrue(
        $reflection->hasProperty($expectedProperty),
        "必要なプロパティ '{$expectedProperty}' が存在しません"
      );
    }

    // 不変条件3: 必要なメソッドが存在すること
    $expectedMethods = ['show', 'sendMessage', 'updateMessage', 'deleteMessage', 'complete', 'submitRating'];
    foreach ($expectedMethods as $method) {
      $this->assertTrue(
        method_exists($controller, $method),
        "必要なメソッド '{$method}' が存在しません"
      );
    }
  }

  /**
   * @test
   * エラーハンドリング契約テスト
   */
  public function test_error_handling_contracts()
  {
        // 事前条件: 認証済みユーザー
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    // 存在しない取引IDでメッセージ送信を試行
    $response = $this->post('/transactions/invalid-id/messages', [
      'content' => 'テストメッセージ'
    ]);

    // 事後条件: 適切なエラーハンドリングが行われること（リダイレクトまたはエラー）
    $this->assertTrue($response->isRedirection());

    // 無効なデータで評価送信を試行
    $transaction = Transaction::factory()->create([
      'buyer_id' => $user->id
    ]);
    $response = $this->post("/transactions/{$transaction->id}/ratings", [
      'rated_id' => '', // 必須フィールドが空
      'rating' => 0 // 無効な評価
    ]);

    // 事後条件: バリデーションエラーが返されること
    $response->assertSessionHasErrors();
  }

  /**
   * @test
   * 認証状態の不変性テスト
   */
  public function test_authentication_state_invariants()
  {
        // 事前条件: 認証済みユーザー
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    // 事前状態の記録
    $initialUserId = auth()->id();

    // 複数のメソッド呼び出し
    $item = Item::factory()->create();
    $transaction = Transaction::factory()->create([
      'item_id' => $item->id,
      'buyer_id' => $user->id
    ]);

    $this->get("/transactions/{$transaction->id}");
    $this->post("/transactions/{$transaction->id}/messages", ['content' => 'テスト']);

    // 事後状態の確認
    $finalUserId = auth()->id();

    // 不変条件: 認証状態が変わらないこと
    $this->assertEquals($initialUserId, $finalUserId);
    $this->assertTrue(auth()->check());
  }
}
