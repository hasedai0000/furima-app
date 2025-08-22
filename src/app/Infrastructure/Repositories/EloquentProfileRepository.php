<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Profile\Entities\Profile as ProfileEntity;
use App\Domain\Profile\Repositories\ProfileRepositoryInterface;
use App\Domain\Profile\ValueObjects\ProfileImgUrl;
use App\Domain\Profile\ValueObjects\ProfilePostCode;
use App\Models\Profile;

class EloquentProfileRepository implements ProfileRepositoryInterface
{
    /**
     * プロフィールを取得
     *
     * @param string $userId
     * @return ProfileEntity|null
     */
    public function findByUserId(string $userId): ?ProfileEntity
    {
        $eloquentProfile = Profile::where('user_id', $userId)->first();

        if (! $eloquentProfile) {
            return null;
        }

        return new ProfileEntity(
            $eloquentProfile->id,
            $eloquentProfile->user_id,
            $eloquentProfile->img_url ? new ProfileImgUrl($eloquentProfile->img_url) : null,
            new ProfilePostCode($eloquentProfile->postcode),
            $eloquentProfile->address,
            $eloquentProfile->building_name
        );
    }

    /**
     * 永続化
     *
     * @param ProfileEntity $profile
     * @return void
     */
    public function save(ProfileEntity $profile): void
    {
        $eloquentProfile = Profile::where('user_id', $profile->getUserId())->first();

        if ($eloquentProfile) {
            // 更新処理
            $eloquentProfile->user_id = $profile->getUserId();
            $eloquentProfile->img_url = $profile->getImgUrl() ? $profile->getImgUrl()->value() : null;
            $eloquentProfile->postcode = $profile->getPostcode()->value();
            $eloquentProfile->address = $profile->getAddress();
            $eloquentProfile->building_name = $profile->getBuildingName();
            $eloquentProfile->save();
        } else {
            // 新規作成処理
            $eloquentProfile = new Profile();
            $eloquentProfile->id = $profile->getId();
            $eloquentProfile->user_id = $profile->getUserId();
            $eloquentProfile->img_url = $profile->getImgUrl() ? $profile->getImgUrl()->value() : null;
            $eloquentProfile->postcode = $profile->getPostcode()->value();
            $eloquentProfile->address = $profile->getAddress();
            $eloquentProfile->building_name = $profile->getBuildingName();
            $eloquentProfile->save();
        }
    }
}
