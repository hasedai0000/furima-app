<?php

namespace App\Domain\Item\Repositories;

use App\Domain\Item\Entities\Item as ItemEntity;

interface ItemRepositoryInterface
{
  public function findAll(): array;
}
