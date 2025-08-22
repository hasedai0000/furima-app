<?php

namespace App\Domain\Profile\Repositories;

use App\Domain\Profile\Entities\Profile as ProfileEntity;

interface ProfileRepositoryInterface
{
    public function findByUserId(string $userId): ?ProfileEntity;
    public function save(ProfileEntity $profile): void;
}
