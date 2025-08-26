<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\UserService;
use App\Domain\User\Entities\User as UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserEmail;
use App\Domain\User\ValueObjects\UserPassword;
use Mockery;
use Tests\TestCase;

class UserServiceTest extends TestCase
{

 private UserService $userService;
 private UserRepositoryInterface $mockUserRepository;

 protected function setUp(): void
 {
  parent::setUp();
  $this->mockUserRepository = Mockery::mock(UserRepositoryInterface::class);
  $this->userService = new UserService($this->mockUserRepository);
 }

 protected function tearDown(): void
 {
  Mockery::close();
  parent::tearDown();
 }

 /**
  * @test
  * getUser() 事前条件検証: userIdは空文字列ではない
  * 事後条件検証: UserEntity|nullを返す
  */
 public function getUser_retrieves_user_with_valid_user_id()
 {
  $userId = '123e4567-e89b-12d3-a456-426614174000';
  $userEntity = new UserEntity(
   $userId,
   'Test User',
   new UserEmail('test@example.com'),
   new UserPassword('password123')
  );

  // リポジトリのモック設定
  $this->mockUserRepository
   ->shouldReceive('findById')
   ->once()
   ->with($userId)
   ->andReturn($userEntity);

  $result = $this->userService->getUser($userId);

  // 事後条件: UserEntityが返される
  $this->assertInstanceOf(UserEntity::class, $result);
  $this->assertEquals($userId, $result->getId());
  $this->assertEquals('Test User', $result->getName());
 }

 /**
  * @test
  * getUser() 存在しないユーザーの場合はnullを返すこと
  */
 public function getUser_returns_null_for_non_existent_user()
 {
  $userId = '999e4567-e89b-12d3-a456-426614174999';

  // リポジトリのモック設定（存在しないユーザー）
  $this->mockUserRepository
   ->shouldReceive('findById')
   ->once()
   ->with($userId)
   ->andReturn(null);

  $result = $this->userService->getUser($userId);

  // 事後条件: nullが返される
  $this->assertNull($result);
 }

 /**
  * @test
  * updateUserName() 事前条件検証: userIdとnameは空文字列ではない
  * 事後条件検証: 更新されたUserEntityを返す
  */
 public function updateUserName_updates_user_name_successfully()
 {
  $userId = '123e4567-e89b-12d3-a456-426614174000';
  $originalName = 'Original Name';
  $newName = 'Updated Name';

  $originalUser = new UserEntity(
   $userId,
   $originalName,
   new UserEmail('test@example.com'),
   new UserPassword('password123')
  );

  $updatedUser = new UserEntity(
   $userId,
   $newName,
   new UserEmail('test@example.com'),
   new UserPassword('password123')
  );

  // リポジトリのモック設定
  $this->mockUserRepository
   ->shouldReceive('findById')
   ->once()
   ->with($userId)
   ->andReturn($originalUser);

  $this->mockUserRepository
   ->shouldReceive('save')
   ->once()
   ->with(Mockery::on(function (UserEntity $user) use ($userId, $newName) {
    return $user->getId() === $userId && $user->getName() === $newName;
   }));

  $result = $this->userService->updateUserName($userId, $newName);

  // 事後条件: 更新されたUserEntityが返される
  $this->assertInstanceOf(UserEntity::class, $result);
  $this->assertEquals($userId, $result->getId());
  $this->assertEquals($newName, $result->getName());

  // 不変条件: ID、メール、パスワードは変更されない
  $this->assertEquals($originalUser->getId(), $result->getId());
  $this->assertEquals($originalUser->getEmail()->value(), $result->getEmail()->value());
  $this->assertEquals($originalUser->getPassword()->value(), $result->getPassword()->value());
 }

 /**
  * @test
  * updateUserName() 事前条件違反: 存在しないユーザーの場合
  * 事後条件検証: 例外が投げられる
  */
 public function updateUserName_throws_exception_for_non_existent_user()
 {
  $userId = '999e4567-e89b-12d3-a456-426614174999';
  $newName = 'New Name';

  // リポジトリのモック設定（存在しないユーザー）
  $this->mockUserRepository
   ->shouldReceive('findById')
   ->once()
   ->with($userId)
   ->andReturn(null);

  // saveメソッドは呼ばれない
  $this->mockUserRepository->shouldNotReceive('save');

  // 事前条件違反により例外が投げられることを期待
  $this->expectException(\Exception::class);
  $this->expectExceptionMessage('ユーザー名の更新に失敗しました。');

  $this->userService->updateUserName($userId, $newName);
 }

 /**
  * @test
  * updateUserName() リポジトリで例外が発生した場合
  */
 public function updateUserName_throws_exception_when_repository_error_occurs()
 {
  $userId = '123e4567-e89b-12d3-a456-426614174000';
  $newName = 'New Name';

  $originalUser = new UserEntity(
   $userId,
   'Original Name',
   new UserEmail('test@example.com'),
   new UserPassword('password123')
  );

  // リポジトリのモック設定
  $this->mockUserRepository
   ->shouldReceive('findById')
   ->once()
   ->with($userId)
   ->andReturn($originalUser);

  $this->mockUserRepository
   ->shouldReceive('save')
   ->once()
   ->andThrow(new \Exception('Database error'));

  // 例外が適切にキャッチされ、アプリケーション例外として再投げされることを期待
  $this->expectException(\Exception::class);
  $this->expectExceptionMessage('ユーザー名の更新に失敗しました。');

  $this->userService->updateUserName($userId, $newName);
 }

 /**
  * @test
  * 事前条件検証: 空文字列のuserIdは受け入れない（境界値テスト）
  */
 public function updateUserName_rejects_empty_user_id()
 {
  $userId = '';
  $newName = 'New Name';

  // 空のuserIdでfindByIdが呼ばれる
  $this->mockUserRepository
   ->shouldReceive('findById')
   ->once()
   ->with($userId)
   ->andReturn(null);

  $this->expectException(\Exception::class);
  $this->expectExceptionMessage('ユーザー名の更新に失敗しました。');

  $this->userService->updateUserName($userId, $newName);
 }

 /**
  * @test
  * 不変条件検証: UserServiceの状態は変更されない
  */
 public function maintains_service_state_across_multiple_operations()
 {
  $userId1 = '123e4567-e89b-12d3-a456-426614174001';
  $userId2 = '123e4567-e89b-12d3-a456-426614174002';

  $user1 = new UserEntity(
   $userId1,
   'User 1',
   new UserEmail('user1@example.com'),
   new UserPassword('password123')
  );

  $user2 = new UserEntity(
   $userId2,
   'User 2',
   new UserEmail('user2@example.com'),
   new UserPassword('password123')
  );

  // 複数のリポジトリ呼び出しをモック
  $this->mockUserRepository
   ->shouldReceive('findById')
   ->with($userId1)
   ->andReturn($user1);

  $this->mockUserRepository
   ->shouldReceive('findById')
   ->with($userId2)
   ->andReturn($user2);

  // 複数回の呼び出しでサービスが一貫した動作をすることを確認
  $result1 = $this->userService->getUser($userId1);
  $result2 = $this->userService->getUser($userId2);

  // 不変条件: 各呼び出しで独立した結果が返される
  $this->assertNotEquals($result1->getId(), $result2->getId());
  $this->assertEquals('User 1', $result1->getName());
  $this->assertEquals('User 2', $result2->getName());
 }
}
