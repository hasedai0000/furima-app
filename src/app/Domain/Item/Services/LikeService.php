<?php

namespace App\Domain\Item\Services;

use App\Domain\Item\Repositories\LikeRepositoryInterface;

class LikeService
{
    private LikeRepositoryInterface $likeRepository;

    public function __construct(LikeRepositoryInterface $likeRepository)
    {
        $this->likeRepository = $likeRepository;
    }

    /**
     *　商品詳細にいいねをする(マイリストに追加する)
     *
     * @param string $itemId
     * @return void
     */
    public function like(string $itemId): void
    {
        $this->likeRepository->save($itemId);
    }

    /**
     * いいねを削除する(マイリストから削除する)
     *
     * @param string $itemId
     * @return void
     */
    public function unlike(string $itemId): void
    {
        $this->likeRepository->delete($itemId);
    }

    /**
     * 現在のユーザーが指定された商品にいいねしているかチェック
     *
     * @param string $itemId
     * @return bool
     */
    public function isLiked(string $itemId): bool
    {
        return $this->likeRepository->exists($itemId);
    }
}
