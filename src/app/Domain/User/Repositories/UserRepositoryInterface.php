<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User as UserEntity;

interface UserRepositoryInterface
{
 public function findById(string $userId): ?UserEntity;
}
