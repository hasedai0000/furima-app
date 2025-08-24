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
  return [
   'id' => $item['id'],
   'name' => $item['name'],
   'brandName' => $item['brand_name'],
   'description' => $item['description'],
   'price' => $item['price'],
   'condition' => $item['condition'],
   'imgUrl' => $item['img_url'],
   'isSold' => isset($item['purchases']) && count($item['purchases']) > 0,
  ];
 }
}
