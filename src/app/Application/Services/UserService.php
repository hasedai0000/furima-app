<?php

namespace App\Application\Services;

use App\Domain\User\Entities\User as UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;

class UserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * ユーザーを取得
     *
     */
    public function getUser(string $userId): ?UserEntity
    {
        return $this->userRepository->findById($userId);
    }

    /**
     * ユーザー名を更新
     *
     * @param string $userId
     * @param string $name
     * @return UserEntity
     */
    public function updateUserName(string $userId, string $name): UserEntity
    {
        try {
            $user = $this->getUser($userId);

            if (! $user) {
                throw new \Exception('ユーザーが見つかりません。');
            }

            $user = new UserEntity(
                $user->getId(),
                $name,
                $user->getEmail(),
                $user->getPassword()
            );

            $this->userRepository->save($user);

            return $user;
        } catch (\Exception $e) {
            throw new \Exception('ユーザー名の更新に失敗しました。');
        }
    }
}
