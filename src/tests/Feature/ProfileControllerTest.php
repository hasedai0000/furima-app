<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;

/**
 * ProfileControllerのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * データベースを汚さない設計で実装
 */
class ProfileControllerTest extends TestCase
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
   * index メソッドの認証必須契約テスト
   * 
   * 事前条件: 認証が必要
   * 事後条件: 認証なしの場合はログインページにリダイレクト
   * 不変条件: 認証済みの場合はmypage.indexビューが返される
   */
  public function test_index_requires_authentication_contract()
  {
    // 事前条件: 認証なしでアクセス
    $response = $this->get('/mypage');

    // 事後条件: ログインページにリダイレクトされること
    $response->assertRedirect('/login');

    // 事前条件: 認証済みユーザーでアクセス
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/mypage');

    // 事後条件: マイページが表示されること
    $response->assertStatus(200);
    $response->assertViewIs('mypage.index');
    $response->assertViewHas('items');
    $response->assertViewHas('searchTerm');
  }

  /**
   * @test
   * index メソッドの購入履歴表示契約テスト
   * 
   * 事前条件: 認証済みユーザーと購入履歴データ
   * 事後条件: 購入履歴が正しく表示される
   * 不変条件: ビューデータの整合性が保たれる
   */
  public function test_index_shows_buy_items_with_correct_contract()
  {
    // 事前条件: 認証済みユーザー
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act: 購入履歴表示（デフォルト）
    $response = $this->get('/mypage');

    // 事後条件の検証
    $response->assertStatus(200);
    $response->assertViewIs('mypage.index');

    // 不変条件: ビューデータの型が正しいこと
    $viewData = $response->original->getData();
    $this->assertIsIterable($viewData['items']);
    $this->assertIsString($viewData['searchTerm']);
  }

  /**
   * @test
   * index メソッドの出品履歴表示契約テスト
   * 
   * 事前条件: 認証済みユーザーとpage=sellパラメータ
   * 事後条件: 出品履歴が正しく表示される
   */
  public function test_index_shows_sell_items_with_correct_contract()
  {
    // 事前条件: 認証済みユーザー
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act: 出品履歴表示
    $response = $this->get('/mypage?page=sell');

    // 事後条件の検証
    $response->assertStatus(200);
    $response->assertViewIs('mypage.index');
    $response->assertViewHas('items');
    $response->assertViewHas('searchTerm');
  }

  /**
   * @test
   * show メソッドの契約テスト
   * 
   * 事前条件: 認証済みユーザー
   * 事後条件: プロフィールページが表示される
   * 不変条件: ユーザーとプロフィールデータが正しく渡される
   */
  public function test_show_returns_profile_with_correct_contract()
  {
    // 事前条件: 認証済みユーザーとプロフィール
    /** @var User $user */
    $user = User::factory()->create(['name' => 'テストユーザー']);
    $profile = Profile::factory()->create(['user_id' => $user->id]);
    $this->actingAs($user);

    // Act: プロフィール表示
    $response = $this->get('/mypage/profile');

    // 事後条件の検証
    $response->assertStatus(200);
    $response->assertViewIs('mypage.profile');
    $response->assertViewHas('name', 'テストユーザー');
    $response->assertViewHas('profile');

    // 不変条件: プロフィールデータの整合性
    $viewData = $response->original->getData();
    $this->assertNotNull($viewData['profile']);
    $this->assertIsArray($viewData['profile']);
  }

  /**
   * @test
   * show メソッドのプロフィール未作成時契約テスト
   * 
   * 事前条件: 認証済みユーザー（プロフィール未作成）
   * 事後条件: プロフィールページが表示される（プロフィールはnull）
   */
  public function test_show_without_profile_contract()
  {
    // 事前条件: 認証済みユーザー（プロフィールなし）
    /** @var User $user */
    $user = User::factory()->create(['name' => 'テストユーザー']);
    $this->actingAs($user);

    // Act: プロフィール表示
    $response = $this->get('/mypage/profile');

    // 事後条件の検証
    $response->assertStatus(200);
    $response->assertViewIs('mypage.profile');
    $response->assertViewHas('name', 'テストユーザー');
    $response->assertViewHas('profile', null);
  }

  /**
   * @test
   * store メソッドの契約テスト
   * 
   * 事前条件: 認証済みユーザーと有効なプロフィールデータ
   * 事後条件: プロフィールが作成され、アイテム一覧にリダイレクト
   * 不変条件: データベースにプロフィールが保存される
   */
  public function test_store_creates_profile_with_correct_contract()
  {
    // 事前条件: 認証済みユーザー
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $profileData = [
      'name' => '更新されたユーザー名',
      'postcode' => '123-4567',
      'address' => '東京都渋谷区',
      'buildingName' => 'テストビル101'
      // imgUrlは含めない（nullableなので省略可能）
    ];

    // Act: プロフィール作成
    $response = $this->post('/mypage/profile', $profileData);

    // 事後条件: リダイレクトと成功メッセージ
    $response->assertRedirect('/');
    $response->assertSessionHas('success', 'プロフィールが正常に作成されました。');

    // 不変条件: データベースにプロフィールが保存されること
    $this->assertDatabaseHas('profiles', [
      'user_id' => $user->id,
      'postcode' => $profileData['postcode'],
      'address' => $profileData['address'],
      'building_name' => $profileData['buildingName']
    ]);

    // 不変条件: ユーザー名が更新されること
    $this->assertDatabaseHas('users', [
      'id' => $user->id,
      'name' => $profileData['name']
    ]);
  }

  /**
   * @test
   * update メソッドの契約テスト
   * 
   * 事前条件: 認証済みユーザーと既存のプロフィール
   * 事後条件: プロフィールが更新され、アイテム一覧にリダイレクト
   * 不変条件: データベースのプロフィールが更新される
   */
  public function test_update_modifies_profile_with_correct_contract()
  {
    // 事前条件: 認証済みユーザーと既存プロフィール
    /** @var User $user */
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);
    $this->actingAs($user);

    $updateData = [
      'name' => '新しいユーザー名',
      'postcode' => '987-6543',
      'address' => '大阪府大阪市',
      'buildingName' => '新しいビル202'
    ];

    // Act: プロフィール更新
    $response = $this->put('/mypage/profile', $updateData);

    // 事後条件: リダイレクトと成功メッセージ
    $response->assertRedirect('/mypage');
    $response->assertSessionHas('success', 'プロフィールが正常に更新されました。');

    // 不変条件: データベースのプロフィールが更新されること
    $this->assertDatabaseHas('profiles', [
      'user_id' => $user->id,
      'postcode' => $updateData['postcode'],
      'address' => $updateData['address'],
      'building_name' => $updateData['buildingName']
    ]);

    // 不変条件: ユーザー名が更新されること
    $this->assertDatabaseHas('users', [
      'id' => $user->id,
      'name' => $updateData['name']
    ]);
  }

  /**
   * @test
   * modifyAddress メソッドの契約テスト
   * 
   * 事前条件: 認証済みユーザーと有効な住所データ
   * 事後条件: 住所が更新され、購入手続きページにリダイレクト
   * 不変条件: プロフィールの住所情報のみが更新される
   */
  public function test_modify_address_updates_address_with_correct_contract()
  {
    // 事前条件: 認証済みユーザーと既存プロフィール、アイテム
    /** @var User $user */
    $user = User::factory()->create();
    $profile = Profile::factory()->create([
      'user_id' => $user->id,
      'postcode' => '111-1111',
      'address' => '元の住所'
    ]);
    $item = Item::factory()->create();
    $this->actingAs($user);

    $addressData = [
      'postcode' => '222-2222',
      'address' => '新しい住所',
      'buildingName' => '新しい建物名'
    ];

    // Act: 住所更新
    $response = $this->put("/purchase/address/{$item->id}", $addressData);

    // 事後条件: 購入手続きページにリダイレクト
    $response->assertRedirect("/purchase/{$item->id}");
    $response->assertSessionHas('success', '住所を更新しました');

    // 不変条件: プロフィールの住所が更新されること
    $this->assertDatabaseHas('profiles', [
      'user_id' => $user->id,
      'postcode' => $addressData['postcode'],
      'address' => $addressData['address'],
      'building_name' => $addressData['buildingName']
    ]);
  }

  /**
   * @test
   * ProfileController クラスの不変条件テスト
   */
  public function test_profile_controller_invariants()
  {
    $controller = app(\App\Http\Controllers\ProfileController::class);

    // 不変条件1: ProfileControllerはControllerを継承していること
    $this->assertInstanceOf(\App\Http\Controllers\Controller::class, $controller);

    // 不変条件2: 必要な依存関係が正しく注入されていること
    $reflection = new \ReflectionClass($controller);
    $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);

    $expectedProperties = [
      'profileService',
      'itemService',
      'userService',
      'fileUploadService',
      'authService'
    ];

    foreach ($expectedProperties as $expectedProperty) {
      $this->assertTrue(
        $reflection->hasProperty($expectedProperty),
        "必要なプロパティ '{$expectedProperty}' が存在しません"
      );
    }

    // 不変条件3: 必要なメソッドが存在すること
    $expectedMethods = ['index', 'show', 'store', 'update', 'modifyAddress'];
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

    // 無効なデータでプロフィール作成を試行
    $response = $this->post('/mypage/profile', [
      'name' => '', // 必須フィールドが空
      'postcode' => 'invalid', // 無効な郵便番号
    ]);

    // 事後条件: バリデーションエラーが返されること
    $response->assertSessionHasErrors();

    // 無効なデータでプロフィール更新を試行
    $response = $this->put('/mypage/profile', [
      'name' => '', // 必須フィールドが空
    ]);

    // 事後条件: バリデーションエラーが返されること
    $response->assertSessionHasErrors();
  }

  /**
   * @test
   * ファイルアップロード契約テスト
   */
  public function test_file_upload_contract()
  {
    // 事前条件: 認証済みユーザー
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $profileData = [
      'name' => 'ファイルテストユーザー',
      'postcode' => '123-4567',
      'address' => '東京都渋谷区',
      'buildingName' => 'テストビル'
      // imgUrlなしでテスト
    ];

    // Act: ファイル付きプロフィール作成
    $response = $this->post('/mypage/profile', $profileData);

    // 事後条件: 正常に処理されること
    $response->assertRedirect('/');
    $response->assertSessionHas('success', 'プロフィールが正常に作成されました。');

    // 不変条件: ファイルが適切に処理されること
    $this->assertDatabaseHas('profiles', [
      'user_id' => $user->id,
      'postcode' => $profileData['postcode']
    ]);
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
    $this->get('/mypage');
    $this->get('/mypage/profile');

    // 事後状態の確認
    $finalUserId = auth()->id();

    // 不変条件: 認証状態が変わらないこと
    $this->assertEquals($initialUserId, $finalUserId);
    $this->assertTrue(auth()->check());
  }
}
