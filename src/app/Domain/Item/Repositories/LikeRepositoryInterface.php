<?php

namespace App\Domain\Item\Repositories;

interface LikeRepositoryInterface
{
    public function save(string $itemId): void;
    public function delete(string $itemId): void;
    public function exists(string $itemId): bool;
}
