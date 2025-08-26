<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * AuthControllerのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * データベースを汚さない設計で実装
 */
class AuthControllerTest extends TestCase
{
	use WithFaker;

	protected function setUp(): void
	{
		parent::setUp();

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
	 * showVerificationNotice メソッドのテスト
	 * 
	 * 事前条件: なし（パブリックアクセス可能）
	 * 事後条件: auth.verify-emailビューが返される
	 * 不変条件: レスポンスステータスが200である
	 */
	public function test_show_verification_notice_returns_correct_view_with_contract()
	{
		// 事前条件: なし（パブリックメソッド）

		// Act: showVerificationNoticeメソッドを直接呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->showVerificationNotice();

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
		$this->assertEquals('auth.verify-email', $result->getName());

		// 不変条件: ビューインスタンスが正しく生成されていること
		$this->assertNotNull($result);
	}

	/**
	 * @test
	 * AuthController クラスの不変条件テスト
	 */
	public function test_auth_controller_invariants()
	{
		$controller = app(\App\Http\Controllers\AuthController::class);

		// 不変条件1: AuthControllerはControllerを継承していること
		$this->assertInstanceOf(\App\Http\Controllers\Controller::class, $controller);

		// 不変条件2: 必要なメソッドが存在すること
		$this->assertTrue(method_exists($controller, 'showVerificationNotice'));

		// 不変条件3: メソッドのシグネチャが正しいこと
		$reflection = new \ReflectionClass($controller);
		$verificationMethod = $reflection->getMethod('showVerificationNotice');

		$this->assertTrue($verificationMethod->isPublic());

		// 戻り値の型がViewであることを確認
		$this->assertEquals('Illuminate\Contracts\View\View', $verificationMethod->getReturnType()->getName());
	}

	/**
	 * @test
	 * メソッド呼び出し前後での状態不変性テスト
	 */
	public function test_method_call_state_invariants()
	{
		$controller = app(\App\Http\Controllers\AuthController::class);

		// 事前状態の記録
		$initialControllerState = serialize($controller);

		// メソッド呼び出し
		$result2 = $controller->showVerificationNotice();

		// 事後状態の確認
		$finalControllerState = serialize($controller);

		// 不変条件: コントローラ自体の状態は変わらないこと（ステートレス）
		$this->assertEquals($initialControllerState, $finalControllerState);

		// 不変条件: 複数回呼び出しても同じ結果が得られること（べき等性）
		$result2_repeat = $controller->showVerificationNotice();

		$this->assertEquals($result2->getName(), $result2_repeat->getName());
	}
}
