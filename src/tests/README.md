# データベースを汚さないテスト実装

このプロジェクトのテストは、**データベースを汚さない設計**で実装されています。

## 主な特徴

### 1. 専用テストデータベースの使用

-   テスト実行時は`furima_test`データベースを使用
-   各テスト後にトランザクションロールバックでデータをクリーンアップ
-   本番の`furima`データベースに影響を与えない

```bash
// テスト用のDBを作成
docker compose exec mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS furima_test;"

// マイグレーション
docker compose exec php php artisan migrate --env=testing

// テストを実行
docker compose exec php php artisan test
```

### 2. トランザクションベーステスト

-   各テストメソッドの開始時にトランザクションを開始
-   テスト終了時にロールバックを実行
-   データベースの状態が常にクリーンに保たれる

### 3. モック・スタブの活用

-   外部サービス（Stripe 等）への依存を排除
-   データベース操作を最小限に抑制
-   テストの実行速度向上と信頼性確保

## 使用方法

### 基本的なテストクラス作成

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class ExampleTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // 追加のセットアップ処理
    }

    protected function tearDown(): void
    {
        // 追加のクリーンアップ処理
        parent::tearDown();
    }

    public function test_example()
    {
        // テストロジック
    }
}
```

### MockHelper の使用

```php
public function test_with_mocks()
{
    // PurchaseServiceのモックを作成
    $this->getMockHelper()->mockPurchaseService();

    // StripeSessionのモックを作成
    $this->getMockHelper()->mockStripeSession('paid');

    // テスト実行
    $response = $this->post('/purchase/item-id');

    // アサーション
    $response->assertStatus(200);
}
```

### カスタムモックデータ

```php
public function test_with_custom_mock_data()
{
    // カスタムアイテムデータでモック作成
    $mockItem = $this->getMockHelper()->createMockItem([
        'name' => 'カスタム商品',
        'price' => 2000
    ]);

    // ItemServiceのモックを設定
    $this->getMockHelper()->mockItemService([
        'getItemById' => $mockItem
    ]);
}
```

## テストファイル構成

```
tests/
├── Feature/           # 機能テスト
│   ├── AuthControllerTest.php
│   ├── ItemControllerTest.php
│   ├── ProfileControllerTest.php
│   └── PurchaseControllerTest.php
├── Unit/             # 単体テスト
├── Helpers/          # テストヘルパー
│   └── MockHelper.php
├── TestCase.php      # 基底テストクラス
└── README.md         # このファイル
```

## 重要な注意点

### やるべきこと

-   各テストは独立して実行可能にする
-   モックを使用して外部依存を排除する
-   テストデータはテスト内で作成する
-   断言（assertion）を明確に記述する

### やってはいけないこと

-   `RefreshDatabase`トレイトの使用
-   本番データベース（`furima`）への直接アクセス
-   テスト間でのデータ共有
-   外部 API への実際のリクエスト

## テスト実行

```bash
# 全テスト実行
php artisan test

# 特定のテストクラス実行
php artisan test tests/Feature/PurchaseControllerTest.php

# 特定のテストメソッド実行
php artisan test --filter test_procedure_returns_view_with_correct_contract
```

## データベース設定

テスト実行時は`phpunit.xml`の設定により、自動的に専用テストデータベースが使用されます：

```xml
<server name="DB_CONNECTION" value="mysql"/>
<server name="DB_DATABASE" value="furima_test"/>
```

## 初期セットアップ

テスト用データベースを作成：

```bash
# Dockerコンテナ内でテスト用DB作成
docker compose exec mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS furima_test;"
```

## パフォーマンス

-   専用テストデータベースにより本番環境との分離
-   トランザクションロールバックにより効率的なクリーンアップ
-   モック使用により外部依存を排除
-   MySQL なので本番環境と完全な互換性

この設計により、テストの信頼性確保、本番データベースの保護、そして本番環境との完全な互換性を実現しています。
