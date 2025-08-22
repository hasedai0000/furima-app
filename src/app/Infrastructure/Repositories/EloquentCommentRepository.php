<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Item\Repositories\CommentRepositoryInterface;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    /**
     * コメントを保存する
     *
     * @param string $content
     * @param string $itemId
     * @return void
     */
    public function save(string $content, string $itemId): void
    {
        $eloquentComment = new Comment();
        $eloquentComment->item_id = $itemId;
        $eloquentComment->user_id = Auth::user()->id;
        $eloquentComment->content = $content;
        $eloquentComment->save();
    }
}
