<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\AuthenticationService;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationServiceTest extends TestCase
{

 private AuthenticationService $authService;

 protected function setUp(): void
 {
  parent::setUp();
  $this->authService = new AuthenticationService();
 }

 /**
  * @test
  * isAuthenticated() 事前条件検証: 認証状態に依存する条件はない
  * 事後条件検証: 戻り値はbool型である
  */
 public function isAuthenticated_returns_bool_for_authentication_status()
 {
  // 未認証状態でのテスト
  Auth::shouldReceive('check')->once()->andReturn(false);

  $result = $this->authService->isAuthenticated();

  // 事後条件: 戻り値はbool型
  $this->assertIsBool($result);
  $this->assertFalse($result);
 }

 /**
  * @test
  * isAuthenticated() 認証状態でtrueを返すこと
  */
 public function isAuthenticated_returns_true_when_user_is_authenticated()
 {
  // 認証状態でのテスト
  Auth::shouldReceive('check')->once()->andReturn(true);

  $result = $this->authService->isAuthenticated();

  // 事後条件: 戻り値はbool型のtrue
  $this->assertIsBool($result);
  $this->assertTrue($result);
 }

 /**
  * @test
  * getCurrentUserId() 事前条件検証: 認証状態に依存する条件はない
  * 事後条件検証: 戻り値はstring|null型である
  */
 public function getCurrentUserId_returns_user_id_or_null()
 {
  // 未認証状態
  Auth::shouldReceive('id')->once()->andReturn(null);

  $result = $this->authService->getCurrentUserId();

  // 事後条件: 戻り値はstring|null型
  $this->assertNull($result);
 }

 /**
  * @test
  * getCurrentUserId() 認証状態でユーザーIDを返すこと
  */
 public function getCurrentUserId_returns_user_id_when_authenticated()
 {
  // 認証状態
  $userId = '123e4567-e89b-12d3-a456-426614174000';
  Auth::shouldReceive('id')->once()->andReturn($userId);

  $result = $this->authService->getCurrentUserId();

  // 事後条件: 戻り値はstring型
  $this->assertIsString($result);
  $this->assertEquals($userId, $result);
 }

 /**
  * @test
  * getCurrentUser() 事前条件検証: 認証状態に依存する条件はない
  * 事後条件検証: 戻り値はAuthenticatable|null型である
  */
 public function getCurrentUser_returns_user_or_null()
 {
  // 未認証状態
  Auth::shouldReceive('user')->once()->andReturn(null);

  $result = $this->authService->getCurrentUser();

  // 事後条件: 戻り値はnull
  $this->assertNull($result);
 }

 /**
  * @test
  * getCurrentUser() 認証状態でユーザーオブジェクトを返すこと
  */
 public function getCurrentUser_returns_user_object_when_authenticated()
 {
  // 認証状態（モックユーザー）
  $mockUser = new \stdClass();
  $mockUser->id = '123';
  $mockUser->email = 'test@example.com';

  Auth::shouldReceive('user')->once()->andReturn($mockUser);

  $result = $this->authService->getCurrentUser();

  // 事後条件: 戻り値はオブジェクト
  $this->assertIsObject($result);
  $this->assertEquals($mockUser, $result);
 }

 /**
  * @test
  * requireAuthentication() 事前条件検証: 認証済みユーザーが存在する
  * 事後条件検証: 認証済みの場合はユーザーIDを返す
  */
 public function requireAuthentication_returns_user_id_when_authenticated()
 {
  $userId = '123e4567-e89b-12d3-a456-426614174000';

  // 認証状態をモック
  Auth::shouldReceive('check')->andReturn(true);
  Auth::shouldReceive('id')->andReturn($userId);

  $result = $this->authService->requireAuthentication();

  // 事後条件: 戻り値はstring型のユーザーID
  $this->assertIsString($result);
  $this->assertEquals($userId, $result);
 }

 /**
  * @test
  * requireAuthentication() 事前条件違反: 未認証ユーザーの場合
  * 事後条件検証: 例外が投げられる
  */
 public function requireAuthentication_throws_exception_when_not_authenticated()
 {
  // 未認証状態をモック
  Auth::shouldReceive('check')->andReturn(false);

  // 事前条件違反により例外が投げられることを期待
  $this->expectException(\Exception::class);
  $this->expectExceptionMessage('認証が必要です。');

  $this->authService->requireAuthentication();
 }

 /**
  * @test
  * 不変条件検証: AuthenticationServiceはステートレスであること
  */
 public function returns_consistent_results_for_multiple_calls()
 {
  // 認証状態を固定
  Auth::shouldReceive('check')->times(2)->andReturn(true);
  Auth::shouldReceive('id')->times(2)->andReturn('test-user-id');

  // 複数回呼び出して同じ結果が返ることを確認
  $result1 = $this->authService->isAuthenticated();
  $result2 = $this->authService->isAuthenticated();
  $userId1 = $this->authService->getCurrentUserId();
  $userId2 = $this->authService->getCurrentUserId();

  // 不変条件: ステートレスなので同じ結果が返る
  $this->assertEquals($result1, $result2);
  $this->assertEquals($userId1, $userId2);
 }
}
