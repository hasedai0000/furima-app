<?php

namespace App\Domain\Item\Services;

use App\Domain\Item\Repositories\CommentRepositoryInterface;

class CommentService
{
    private CommentRepositoryInterface $commentRepository;

    public function __construct(CommentRepositoryInterface $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    /**
     *　商品詳細にコメントを投稿する
     *
     * @param array $request
     * @param string $itemId
     * @return void
     */
    public function post(string $content, string $itemId): void
    {
        $this->commentRepository->save($content, $itemId);
    }
}
