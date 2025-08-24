<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemCategorySeeder extends Seeder
{
 /**
  * Run the database seeds.
  *
  * @return void
  */
 public function run()
 {
  $items = Item::all();
  $categories = Category::all();

  if ($items->isEmpty()) {
   $this->command->warn('No items found. Please run ItemSeeder first.');
   return;
  }

  if ($categories->isEmpty()) {
   $this->command->warn('No categories found. Please run CategorySeeder first.');
   return;
  }

  // カテゴリーを配列に変換
  $categoryArray = $categories->toArray();

  // 商品とカテゴリーのマッピング（商品名に基づく）
  $itemCategoryMappings = [
   '腕時計' => ['ファッション', 'アクセサリー'],
   'HDD' => ['家電'],
   '玉ねぎ3束' => ['キッチン'],
   '革靴' => ['ファッション', 'メンズ'],
   'ノートPC' => ['家電'],
   'マイク' => ['家電'],
   'ショルダーバッグ' => ['ファッション', 'レディース'],
   'タンブラー' => ['キッチン'],
   'コーヒーミル' => ['キッチン'],
   'メイクセット' => ['コスメ', 'レディース'],
  ];

  $insertData = [];

  foreach ($items as $item) {
   // 商品名に基づいてカテゴリーを取得
   $categoryNames = $itemCategoryMappings[$item->name] ?? [];

   // カテゴリー名が見つからない場合はランダムにカテゴリーを選択
   if (empty($categoryNames)) {
    $randomCategory = $categoryArray[array_rand($categoryArray)];
    $categoryNames = [$randomCategory['name']];
   }

   foreach ($categoryNames as $categoryName) {
    // カテゴリー名からカテゴリーIDを取得
    $category = $categories->firstWhere('name', $categoryName);

    if ($category) {
     $insertData[] = [
      'item_id' => $item->id,
      'category_id' => $category->id,
      'created_at' => now(),
      'updated_at' => now(),
     ];
    }
   }
  }

  // 重複チェックとバッチインサート
  foreach ($insertData as $data) {
   // 既存の関連をチェック
   $exists = DB::table('item_categories')
    ->where('item_id', $data['item_id'])
    ->where('category_id', $data['category_id'])
    ->exists();

   if (!$exists) {
    DB::table('item_categories')->insert($data);
   }
  }

  // 作成された関連数をログに出力
  $relationCount = DB::table('item_categories')->count();
  $this->command->info("Created {$relationCount} item-category relationships successfully!");
 }
}
