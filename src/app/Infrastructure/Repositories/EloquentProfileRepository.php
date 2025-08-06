<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Profile\Entities\Profile as ProfileEntity;
use App\Domain\Profile\Repositories\ProfileRepositoryInterface;
use App\Models\Profile;

class EloquentProfileRepository implements ProfileRepositoryInterface
{
  /**
   * 永続化
   *
   * @param ProfileEntity $profile
   * @return void
   */
  public function save(ProfileEntity $profile): void
  {
    $eloquentProfile = Profile::find($profile->getId());

    if (!$eloquentProfile) {
      // 新規作成処理
      $eloquentProfile = new Profile();
      $eloquentProfile->id = $profile->getId();
      $eloquentProfile->user_id = $profile->getUserId();
      $eloquentProfile->img_url = $profile->getImgUrl() ? $profile->getImgUrl()->value() : null;
      $eloquentProfile->postcode = $profile->getPostcode()->value();
      $eloquentProfile->address = $profile->getAddress();
      $eloquentProfile->building_name = $profile->getBuildingName();
      $eloquentProfile->save();
    } else {
      // 更新処理
      $eloquentProfile->user_id = $profile->getUserId();
      $eloquentProfile->img_url = $profile->getImgUrl() ? $profile->getImgUrl()->value() : null;
      $eloquentProfile->postcode = $profile->getPostcode()->value();
      $eloquentProfile->address = $profile->getAddress();
      $eloquentProfile->building_name = $profile->getBuildingName();
      $eloquentProfile->save();
    }
  }
}
