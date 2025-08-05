<?php

namespace App\Domain\Profile\Repositories;

use App\Domain\Profile\Entities\Profile;

interface ProfileRepositoryInterface
{
 public function save(Profile $profile): void;
}
