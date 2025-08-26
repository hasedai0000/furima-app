<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Tests\Helpers\MockHelper;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * データベースの初期化が完了しているかどうかのフラグ
     */
    protected static bool $databaseInitialized = false;

    /**
     * @var MockHelper モックヘルパーのインスタンス
     */
    protected MockHelper $mockHelper;

    /**
     * 各テストメソッドの実行前に呼ばれる
     */
    protected function setUp(): void
    {
        parent::setUp();

        // モックヘルパーの初期化
        $this->mockHelper = new MockHelper($this->app);

        // データベースの初期化（最初のテストでのみ実行）
        $this->initializeDatabase();

        // 各テストの開始時にトランザクションを開始
        DB::beginTransaction();
    }

    /**
     * 各テストメソッドの実行後に呼ばれる
     */
    protected function tearDown(): void
    {
        // テスト後にトランザクションをロールバック
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        // モックのクリーンアップ
        $this->resetMocks();

        parent::tearDown();
    }

    /**
     * データベースの初期化
     * MySQL専用テストデータベースにマイグレーションを実行
     */
    protected function initializeDatabase(): void
    {
        if (!static::$databaseInitialized) {
            // テスト用データベースのマイグレーション実行
            Artisan::call('migrate:fresh', ['--database' => 'mysql', '--force' => true]);

            static::$databaseInitialized = true;
        }
    }

    /**
     * テスト用のクリーンな状態でデータベースをリセット
     * 必要に応じてテストから明示的に呼び出す
     */
    protected function refreshDatabase(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        Artisan::call('migrate:fresh', ['--database' => 'mysql', '--force' => true]);

        DB::beginTransaction();
    }

    /**
     * モックの自動クリーンアップを提供
     */
    protected function resetMocks(): void
    {
        if (isset($this->mockHelper)) {
            $this->mockHelper->clearAllMocks();
        }

        if (class_exists(\Mockery::class)) {
            \Mockery::close();
        }
    }

    /**
     * MockHelperのインスタンスを取得
     */
    protected function getMockHelper(): MockHelper
    {
        return $this->mockHelper;
    }
}
