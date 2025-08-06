<?php

namespace App\Application\Services;

use App\Domain\User\Entities\User as UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;

class UserService
{
  private $userRepository;

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
}
