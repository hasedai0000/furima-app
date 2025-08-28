<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Item\Entities\Item as ItemEntity;
use App\Domain\Item\Repositories\ItemRepositoryInterface;
use App\Domain\Item\ValueObjects\ItemCondition;
use App\Domain\Item\ValueObjects\ItemImgUrl;
use App\Models\Item;

class EloquentItemRepository implements ItemRepositoryInterface
{
    /**
     * 商品一覧を取得（購入済み商品も含む）
     *
     * @param string $searchTerm
     * @return array
     */
    public function findAll(string $searchTerm): array
    {
        return Item::with('purchases')
            ->where('name', 'LIKE', '%' . $searchTerm . '%')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * 指定されたユーザーが出品した商品を除外して商品一覧を取得
     *
     * @param string $userId
     * @param string $searchTerm
     * @return array
     */
    public function findAllExcludingUser(string $userId, string $searchTerm): array
    {
        return Item::with('purchases')
            ->where('name', 'LIKE', '%' . $searchTerm . '%')
            ->where('user_id', '!=', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * マイリストの商品を取得
     *
     * @param string $userId
     * @param string $searchTerm
     * @return array
     */
    public function findMyListItems(string $userId, string $searchTerm): array
    {
        return Item::with('purchases')
            ->whereHas('likes', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('name', 'LIKE', '%' . $searchTerm . '%')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * 自分が出品した商品を取得
     *
     * @param string $userId
     * @param string $searchTerm
     * @return array
     */
    public function findMySellItems(string $userId, string $searchTerm): array
    {
        return Item::with('purchases')
            ->where('user_id', '=', $userId)
            ->where('name', 'LIKE', '%' . $searchTerm . '%')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * 自分が購入した商品を取得
     *
     * @param string $userId
     * @param string $searchTerm
     * @return array
     */
    public function findMyBuyItems(string $userId, string $searchTerm): array
    {
        return Item::with('purchases')
            ->whereHas('purchases', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('name', 'LIKE', '%' . $searchTerm . '%')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * 商品詳細を取得
     *
     * @param string $id
     * @return ItemEntity|null
     */
    public function findById(string $id): ?ItemEntity
    {
        $eloquentItem = Item::with('purchases', 'categories', 'comments.user.profile', 'likes')->find($id);

        if (! $eloquentItem) {
            return null;
        }

        // conditionの値を適切に処理する
        $conditionLabel = $this->getConditionLabel($eloquentItem->condition);

        return new ItemEntity(
            $eloquentItem->id,
            $eloquentItem->user_id,
            $eloquentItem->name,
            $eloquentItem->brand_name ?? '',
            $eloquentItem->description,
            (int) $eloquentItem->price,
            $conditionLabel,
            new ItemImgUrl($eloquentItem->img_url),
            $eloquentItem->purchases->toArray() ? true : false,
            $eloquentItem->categories->toArray(),
            $eloquentItem->comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'profile_img_url' => $comment->user->profile ? $comment->user->profile->img_url : null,
                    ]
                ];
            })->toArray(),
            $eloquentItem->likes->toArray()
        );
    }

    /**
     * 商品の状態を適切なラベルに変換する
     *
     * @param string $condition
     * @return string
     */
    private function getConditionLabel(string $condition): string
    {
        // まず英語のキーとして存在するかチェック
        if (array_key_exists($condition, ItemCondition::LABELS)) {
            return ItemCondition::LABELS[$condition];
        }

        // 日本語のラベルとして存在するかチェック
        if (in_array($condition, ItemCondition::LABELS, true)) {
            return $condition;
        }

        // どちらでもない場合は空文字を返す
        return '';
    }

    /**
     * 商品を作成する
     *
     * @param ItemEntity $item
     * @return ItemEntity
     */
    public function save(ItemEntity $item): void
    {
        $eloquentItem = Item::where('id', $item->getId())->first();

        if ($eloquentItem) {
            // 更新処理
            $eloquentItem->user_id = $item->getUserId();
            $eloquentItem->name = $item->getName();
            $eloquentItem->brand_name = $item->getBrandName();
            $eloquentItem->description = $item->getDescription();
            $eloquentItem->price = $item->getPrice();
            $eloquentItem->condition = $item->getCondition();
            $eloquentItem->img_url = $item->getImgUrl()->value();
            $eloquentItem->save();
        } else {
            // 新規作成処理
            $eloquentItem = new Item();
            $eloquentItem->id = $item->getId();
            $eloquentItem->user_id = $item->getUserId();
            $eloquentItem->name = $item->getName();
            $eloquentItem->brand_name = $item->getBrandName();
            $eloquentItem->description = $item->getDescription();
            $eloquentItem->price = $item->getPrice();
            $eloquentItem->condition = $item->getCondition();
            $eloquentItem->img_url = $item->getImgUrl()->value();
            $eloquentItem->save();
        }
    }
}
