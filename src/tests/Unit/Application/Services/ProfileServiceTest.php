<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\ProfileService;
use App\Domain\Profile\Entities\Profile as ProfileEntity;
use App\Domain\Profile\Repositories\ProfileRepositoryInterface;
use App\Domain\Profile\ValueObjects\ProfileImgUrl;
use App\Domain\Profile\ValueObjects\ProfilePostCode;
use Mockery;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
  private ProfileService $profileService;
  private ProfileRepositoryInterface $mockProfileRepository;

  protected function setUp(): void
  {
    parent::setUp();
    $this->mockProfileRepository = Mockery::mock(ProfileRepositoryInterface::class);
    $this->profileService = new ProfileService($this->mockProfileRepository);
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  /**
   * @test
   * getProfile() 存在しないプロフィールの場合はnullを返すこと
   */
  public function getProfile_returns_null_for_non_existent_profile()
  {
    $userId = '999e4567-e89b-12d3-a456-426614174999';

    // リポジトリのモック設定（存在しないプロフィール）
    $this->mockProfileRepository
      ->shouldReceive('findByUserId')
      ->once()
      ->with($userId)
      ->andReturn(null);

    $result = $this->profileService->getProfile($userId);

    // 事後条件: nullが返される
    $this->assertNull($result);
  }

  /**
   * @test
   * createProfile() 画像URLがnullの場合も正常に処理すること
   */
  public function createProfile_handles_null_image_url_correctly()
  {
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $imgUrl = null;
    $postcode = '123-4567';
    $address = 'Tokyo';
    $buildingName = 'Building 1';

    $this->mockProfileRepository
      ->shouldReceive('save')
      ->once()
      ->with(Mockery::on(function (ProfileEntity $profile) use ($userId) {
        return $profile->getUserId() === $userId &&
          $profile->getImgUrl() === null;
      }));

    $result = $this->profileService->createProfile($userId, $imgUrl, $postcode, $address, $buildingName);

    // 事後条件: 画像URLがnullでもProfileEntityが作成される
    $this->assertInstanceOf(ProfileEntity::class, $result);
    $this->assertNull($result->getImgUrl());
  }

  /**
   * @test
   * updateProfile() 事前条件違反: 存在しないプロフィールの場合
   * 事後条件検証: 例外が投げられる
   */
  public function updateProfile_throws_exception_for_non_existent_profile()
  {
    $userId = '999e4567-e89b-12d3-a456-426614174999';

    // リポジトリのモック設定（存在しないプロフィール）
    $this->mockProfileRepository
      ->shouldReceive('findByUserId')
      ->once()
      ->with($userId)
      ->andReturn(null);

    // saveメソッドは呼ばれない
    $this->mockProfileRepository->shouldNotReceive('save');

    // 事前条件違反により例外が投げられることを期待
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('プロフィールが見つかりません。');

    $this->profileService->updateProfile($userId, 'new.jpg', '987-6543', 'Osaka', 'Building 2');
  }

  /**
   * @test
   * 事前条件検証: 郵便番号の形式が正しくない場合
   */
  public function createProfile_throws_exception_for_invalid_postcode()
  {
    $userId = '123e4567-e89b-12d3-a456-426614174000';
    $invalidPostcode = 'invalid-postcode';

    // PostCodeValueObjectで例外が投げられることを期待
    $this->expectException(\Exception::class);

    $this->profileService->createProfile($userId, 'storage/profile.jpg', $invalidPostcode, 'Tokyo', 'Building 1');
  }
}
