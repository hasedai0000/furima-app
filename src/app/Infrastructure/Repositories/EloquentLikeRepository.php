<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Item\Repositories\LikeRepositoryInterface;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;

class EloquentLikeRepository implements LikeRepositoryInterface
{
    /**
     * いいねを保存する（マイリストに追加する）
     *
     * @param string $itemId
     * @return void
     */
    public function save(string $itemId): void
    {
        // 既にいいねしている場合は何もしない
        if ($this->exists($itemId)) {
            return;
        }

        $eloquentLike = new Like();
        $eloquentLike->item_id = $itemId;
        $eloquentLike->user_id = Auth::user()->id;
        $eloquentLike->save();
    }

    /**
     * いいねを削除する（マイリストから削除する）
     *
     * @param string $itemId
     * @return void
     */
    public function delete(string $itemId): void
    {
        Like::where('user_id', Auth::user()->id)
          ->where('item_id', $itemId)
          ->delete();
    }

    /**
     * 現在のユーザーが指定された商品にいいねしているかチェック
     *
     * @param string $itemId
     * @return bool
     */
    public function exists(string $itemId): bool
    {
        return Like::where('user_id', Auth::user()->id)
          ->where('item_id', $itemId)
          ->exists();
    }
}
