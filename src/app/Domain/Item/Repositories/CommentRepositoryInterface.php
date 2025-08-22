<?php

namespace App\Domain\Item\Repositories;

interface CommentRepositoryInterface
{
    public function save(string $content, string $itemId): void;
}
