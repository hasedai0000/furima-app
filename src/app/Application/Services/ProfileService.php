<?php

namespace App\Application\Services;

use App\Domain\Profile\Entities\Profile as ProfileEntity;
use App\Domain\Profile\Repositories\ProfileRepositoryInterface;
use App\Domain\Profile\ValueObjects\ProfileImgUrl;
use App\Domain\Profile\ValueObjects\ProfilePostCode;
use Illuminate\Support\Str;

class ProfileService
{
    private ProfileRepositoryInterface $profileRepository;

    public function __construct(
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->profileRepository = $profileRepository;
    }

    /**
     * プロフィールを取得
     *
     * @param string $userId
     * @return ProfileEntity
     */
    public function getProfile(string $userId): ?ProfileEntity
    {
        return $this->profileRepository->findByUserId($userId);
    }

    /**
     * プロフィール作成
     *
     * @param string $userId
     * @param string|null $imgUrl
     * @param string $postcode
     * @param string $address
     * @param string|null $buildingName
     * @return ProfileEntity
     */
    public function createProfile(
        string $userId,
        ?string $imgUrl,
        string $postcode,
        string $address,
        ?string $buildingName
    ): ProfileEntity {
        $profileImg = $imgUrl ? new ProfileImgUrl($imgUrl) : null;
        $profilePostcode = new ProfilePostCode($postcode);

        $profile = new ProfileEntity(
            Str::uuid()->toString(),
            $userId,
            $profileImg,
            $profilePostcode,
            $address,
            $buildingName ?? ''
        );

        $this->profileRepository->save($profile);

        return $profile;
    }

    /**
     * プロフィール更新
     *
     * @param string $userId
     * @param string|null $imgUrl
     * @param string $postcode
     * @param string $address
     * @param string|null $buildingName
     * @return ProfileEntity
     */
    public function updateProfile(
        string $userId,
        ?string $imgUrl,
        string $postcode,
        string $address,
        ?string $buildingName
    ): ProfileEntity {
        // 既存のプロフィールを取得
        $existingProfile = $this->profileRepository->findByUserId($userId);

        if (! $existingProfile) {
            throw new \Exception('プロフィールが見つかりません。');
        }

        // 画像の処理
        $profileImg = null;
        if ($imgUrl !== null) {
            // 新しい画像がアップロードされた場合
            $profileImg = new ProfileImgUrl($imgUrl);
        } else {
            // 画像がアップロードされなかった場合は既存の画像を保持
            $profileImg = $existingProfile->getImgUrl();
        }

        $profilePostcode = new ProfilePostCode($postcode);

        // 既存のプロフィールのIDを使用して更新
        $profile = new ProfileEntity(
            $existingProfile->getId(),
            $userId,
            $profileImg,
            $profilePostcode,
            $address,
            $buildingName ?? ''
        );

        $this->profileRepository->save($profile);

        return $profile;
    }
}
