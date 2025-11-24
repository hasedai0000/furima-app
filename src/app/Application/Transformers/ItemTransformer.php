<?php

namespace App\Application\Transformers;

class ItemTransformer
{
  /**
   * データベースの商品データをビュー用の配列形式に変換
   *
   * @param array $items
   * @return array
   */
  public static function transformItems(array $items): array
  {
    if (empty($items)) {
      return [];
    }

    return array_map([self::class, 'transformItem'], $items);
  }

  /**
   * 単一の商品データをビュー用の配列形式に変換
   *
   * @param array $item
   * @return array
   */
  public static function transformItem(array $item): array
  {
    // 取引が完了している場合のみ売却済み
    $hasCompletedTransaction = false;
    if (isset($item['transactions']) && is_array($item['transactions'])) {
      foreach ($item['transactions'] as $transaction) {
        if (isset($transaction['status']) && $transaction['status'] === 'completed') {
          $hasCompletedTransaction = true;
          break;
        }
      }
    }

    return [
      'id' => $item['id'],
      'name' => $item['name'],
      'brandName' => $item['brand_name'],
      'description' => $item['description'],
      'price' => $item['price'],
      'condition' => $item['condition'],
      'imgUrl' => $item['img_url'],
      'isSold' => $hasCompletedTransaction,
    ];
  }
}
