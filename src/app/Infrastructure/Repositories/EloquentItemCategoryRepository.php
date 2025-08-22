<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Item\Repositories\ItemCategoryRepositoryInterface;
use App\Models\Item;
use Illuminate\Support\Facades\Log;

class EloquentItemCategoryRepository implements ItemCategoryRepositoryInterface
{
    /**
     * 商品とカテゴリーの関連付けを作成
     *
     * @param string $itemId
     * @param array $categoryIds
     * @return void
     */
    public function attachCategories(string $itemId, array $categoryIds): void
    {
        Log::info('Attaching categories', [
            'itemId' => $itemId,
            'categoryIds' => $categoryIds,
        ]);

        $item = Item::find($itemId);
        if ($item && ! empty($categoryIds)) {
            $item->categories()->attach($categoryIds);
            Log::info('Categories attached successfully');
        } else {
            Log::warning('Failed to attach categories', [
                'itemFound' => $item ? 'yes' : 'no',
                'categoryIdsEmpty' => empty($categoryIds),
            ]);
        }
    }
}
