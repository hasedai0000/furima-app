<?php

namespace App\Infrastructure\Repositories;

use App\Domain\User\Entities\User as UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserEmail;
use App\Domain\User\ValueObjects\UserPassword;
use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * ユーザーを取得
     *
     * @param string $userId
     * @return UserEntity|null
     */
    public function findById(string $userId): ?UserEntity
    {
        $eloquentUser = User::find($userId);

        if (! $eloquentUser) {
            return null;
        }

        return new UserEntity(
            $eloquentUser->id,
            $eloquentUser->name,
            new UserEmail($eloquentUser->email),
            new UserPassword($eloquentUser->password),
        );
    }

    /**
     * ユーザー名を保存
     *
     * @param UserEntity $user
     * @return void
     */
    public function save(UserEntity $user): void
    {
        $eloquentUser = User::find($user->getId());

        if ($eloquentUser) {
            $eloquentUser->name = $user->getName();
            $eloquentUser->save();
        }
    }
}
