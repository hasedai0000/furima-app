<?php

namespace App\Application\Services;

use App\Domain\Profile\Entities\Profile as ProfileEntity;
use App\Domain\Profile\Repositories\ProfileRepositoryInterface;
use App\Domain\Profile\ValueObjects\ProfileImgUrl;
use App\Domain\Profile\ValueObjects\ProfilePostCode;
use Illuminate\Support\Str;


class ProfileService
{
  private $profileRepository;

  public function __construct(
    ProfileRepositoryInterface $profileRepository
  ) {
    $this->profileRepository = $profileRepository;
  }

  /**
   * プロフィール作成
   *
   * @param string $userId
   * @param string|null $imgUrl
   * @param string $postcode
   * @param string $address
   * @param string $buildingName
   * @return ProfileEntity
   */
  public function createProfile(
    string $userId,
    ?string $imgUrl,
    string $postcode,
    string $address,
    string $buildingName
  ): ProfileEntity {
    $profileImg = $imgUrl ? new ProfileImgUrl($imgUrl) : null;
    $profilePostcode = new ProfilePostCode($postcode);

    $profile = new ProfileEntity(
      Str::uuid()->toString(),
      $userId,
      $profileImg,
      $profilePostcode,
      $address,
      $buildingName
    );

    $this->profileRepository->save($profile);

    return $profile;
  }
}
