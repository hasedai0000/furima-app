<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;

/**
 * ItemControllerのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * データベースを汚さない設計で実装
 */
class ItemControllerTest extends TestCase
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
     * index メソッドの基本契約テスト
     * 
     * 事前条件: リクエストパラメータが適切に設定されている
     * 事後条件: items.indexビューが返される
     * 不変条件: レスポンスステータスが200である
     */
    public function test_index_returns_view_with_correct_contract()
    {
        // 事前条件: テストデータの準備
        User::factory()->create();
        Item::factory()->count(3)->create();

        // Act: indexメソッドを呼び出し（実際のルートは '/'）
        $response = $this->get('/');

        // 事後条件の検証
        $response->assertStatus(200);
        $response->assertViewIs('items.index');
        $response->assertViewHas('items');
        $response->assertViewHas('searchTerm');

        // 不変条件: ビューデータの型が正しいこと
        $viewData = $response->original->getData();
        $this->assertIsIterable($viewData['items']);
        $this->assertIsString($viewData['searchTerm']);
    }

    /**
     * @test
     * index メソッドの検索機能契約テスト
     * 
     * 事前条件: 検索パラメータが提供される
     * 事後条件: 検索結果が正しく返される
     */
    public function test_index_search_functionality_contract()
    {
        // 事前条件: 検索対象のアイテムを作成
        Item::factory()->create(['name' => 'テスト商品']);
        Item::factory()->create(['name' => '他の商品']);

        // Act: 検索パラメータ付きでリクエスト（実際のルートは '/'）
        $response = $this->get('/?search=テスト');

        // 事後条件: 検索結果が正しく反映されること
        $response->assertStatus(200);
        $response->assertViewHas('searchTerm', 'テスト');

        // 不変条件: 検索結果の整合性
        $viewData = $response->original->getData();
        $this->assertNotEmpty($viewData['items']);
    }

    /**
     * @test
     * index メソッドのマイリスト表示契約テスト
     * 
     * 事前条件: 認証済みユーザーがいること
     * 事後条件: マイリストが正しく表示される
     */
    public function test_index_mylist_requires_authentication_contract()
    {
        // 事前条件: 認証なしでマイリストにアクセス（実際のルートは '/'）
        $response = $this->get('/?tab=mylist');

        // 事後条件: ログインページにリダイレクトされること
        $response->assertRedirect(route('login'));

        // 事前条件: 認証済みユーザーでアクセス
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/?tab=mylist');

        // 事後条件: マイリストページが表示されること
        $response->assertStatus(200);
        $response->assertViewIs('items.index');
    }

    /**
     * @test
     * detail メソッドの契約テスト
     * 
     * 事前条件: 有効なアイテムIDが提供される
     * 事後条件: アイテム詳細ビューが返される
     * 不変条件: アイテムデータが正しく渡される
     */
    public function test_detail_returns_item_with_correct_contract()
    {
        // 事前条件: アイテムの準備
        $item = Item::factory()->create();

        // Act: detailメソッドを呼び出し（実際のルートパラメータは item_id）
        $response = $this->get("/items/{$item->id}");

        // 事後条件の検証
        $response->assertStatus(200);
        $response->assertViewIs('items.detail');
        $response->assertViewHas('item');

        // 不変条件: アイテムデータの整合性
        $viewData = $response->original->getData();
        $this->assertNotNull($viewData['item']);
        $this->assertEquals($item->id, $viewData['item']['id']);
    }

    /**
     * @test
     * comment メソッドの契約テスト
     * 
     * 事前条件: 認証済みユーザーと有効なコメントデータ
     * 事後条件: コメントが投稿され、詳細ページにリダイレクト
     * 不変条件: データベースにコメントが保存される
     */
    public function test_comment_posts_comment_with_correct_contract()
    {
        // 事前条件: 認証済みユーザーとアイテム
        /** @var User $user */
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        $commentData = [
            'content' => 'これは素晴らしい商品です！'
        ];

        // Act: コメント投稿（実際のルートは /items/{item_id}/comments）
        $response = $this->post("/items/{$item->id}/comments", $commentData);

        // 事後条件: リダイレクトと成功メッセージ
        $response->assertRedirect("/items/{$item->id}");
        $response->assertSessionHas('success', 'コメントを投稿しました。');

        // 不変条件: データベースにコメントが保存されること
        $this->assertDatabaseHas('comments', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'content' => $commentData['content']
        ]);
    }

    /**
     * @test
     * like メソッドの契約テスト（いいね追加）
     * 
     * 事前条件: 認証済みユーザーと未いいねのアイテム
     * 事後条件: いいねが追加され、詳細ページにリダイレクト
     * 不変条件: データベースにいいねが保存される
     */
    public function test_like_adds_like_with_correct_contract()
    {
        // 事前条件: 認証済みユーザーとアイテム
        /** @var User $user */
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        // Act: いいね（実際のルートは /items/{item_id}/likes）
        $response = $this->post("/items/{$item->id}/likes");

        // 事後条件: リダイレクトと成功メッセージ
        $response->assertRedirect("/items/{$item->id}");
        $response->assertSessionHas('success', 'いいねしました。');

        // 不変条件: データベースにいいねが保存されること
        $this->assertDatabaseHas('likes', [
            'item_id' => $item->id,
            'user_id' => $user->id
        ]);
    }

    /**
     * @test
     * like メソッドの契約テスト（いいね取り消し）
     * 
     * 事前条件: 認証済みユーザーと既にいいね済みのアイテム
     * 事後条件: いいねが取り消され、詳細ページにリダイレクト
     * 不変条件: データベースからいいねが削除される
     */
    public function test_like_removes_like_with_correct_contract()
    {
        // 事前条件: 認証済みユーザーとアイテム、既存のいいね
        /** @var User $user */
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        // 事前にいいねを追加
        $this->post("/items/{$item->id}/likes");

        // Act: いいね取り消し
        $response = $this->post("/items/{$item->id}/likes");

        // 事後条件: リダイレクトと成功メッセージ
        $response->assertRedirect("/items/{$item->id}");
        $response->assertSessionHas('success', 'いいねを取り消しました。');

        // 不変条件: データベースからいいねが削除されること
        $this->assertDatabaseMissing('likes', [
            'item_id' => $item->id,
            'user_id' => $user->id
        ]);
    }

    /**
     * @test
     * sell メソッドの契約テスト
     * 
     * 事前条件: 認証済みユーザー
     * 事後条件: 売却ページビューが返される
     * 不変条件: カテゴリと商品状態データが含まれる
     */
    public function test_sell_returns_view_with_required_data_contract()
    {
        // 事前条件: 認証済みユーザーとカテゴリデータの準備
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $categories = Category::factory()->count(3)->create();

        // Act: sellメソッドを呼び出し
        $response = $this->get('/sell');

        // 事後条件の検証
        $response->assertStatus(200);
        $response->assertViewIs('items.sell');
        $response->assertViewHas('categories');
        $response->assertViewHas('itemConditions');

        // 不変条件: 必要なデータが正しく渡されること
        $viewData = $response->original->getData();
        $this->assertIsIterable($viewData['categories']);
        $this->assertIsArray($viewData['itemConditions']);
    }

    /**
     * @test
     * store メソッドの契約テスト
     * 
     * 事前条件: 認証済みユーザーと有効な商品データ
     * 事後条件: 商品が作成され、一覧ページにリダイレクト
     * 不変条件: データベースに商品が保存される
     */
    public function test_store_creates_item_with_correct_contract()
    {
        // 事前条件: 認証済みユーザーとカテゴリ
        /** @var User $user */
        $user = User::factory()->create();
        $categories = Category::factory()->count(2)->create();
        $this->actingAs($user);

        // ファイルアップロードサービスをモック
        $this->mock(\App\Application\Contracts\FileUploadServiceInterface::class, function ($mock) {
            $mock->shouldReceive('upload')->once()->andReturn('storage/images/test-image.jpg');
        });

        // 画像ファイルを使用（簡単なファイル作成）
        $itemData = [
            'name' => 'テスト商品',
            'brand_name' => 'テストブランド',
            'description' => 'これはテスト商品です',
            'price' => 1000,
            'condition' => 'good',
            'category_ids' => $categories->pluck('id')->map(function ($id) {
                return (string)$id;
            })->toArray(),
            'imgUrl' => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg')
        ];

        // Act: 商品作成（実際のルートは /sell）
        $response = $this->post('/sell', $itemData);

        // 事後条件: リダイレクトと成功メッセージ
        $response->assertRedirect('/');
        $response->assertSessionHas('success', '商品を出品しました。');

        // 不変条件: データベースに商品が保存されること
        $this->assertDatabaseHas('items', [
            'name' => $itemData['name'],
            'brand_name' => $itemData['brand_name'],
            'price' => $itemData['price'],
            'user_id' => $user->id
        ]);
    }

    /**
     * @test
     * ItemController クラスの不変条件テスト
     */
    public function test_item_controller_invariants()
    {
        $controller = app(\App\Http\Controllers\ItemController::class);

        // 不変条件1: ItemControllerはControllerを継承していること
        $this->assertInstanceOf(\App\Http\Controllers\Controller::class, $controller);

        // 不変条件2: 必要な依存関係が正しく注入されていること
        $reflection = new \ReflectionClass($controller);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);

        $expectedProperties = [
            'itemService',
            'commentService',
            'likeService',
            'categoryService',
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
        $expectedMethods = ['index', 'detail', 'comment', 'like', 'sell', 'store'];
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

        // 存在しないアイテムIDでコメント投稿を試行
        $response = $this->post('/items/invalid-id/comments', [
            'content' => 'テストコメント'
        ]);

        // 事後条件: 適切なエラーハンドリングが行われること（404など）
        $this->assertTrue($response->status() === 404 || $response->isRedirection());

        // 無効なデータで商品作成を試行
        $response = $this->post('/sell', [
            'name' => '', // 必須フィールドが空
        ]);

        // 事後条件: バリデーションエラーが返されること
        $response->assertSessionHasErrors();
    }
}
