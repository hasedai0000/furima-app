<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
 /**
  * Run the database seeds.
  *
  * @return void
  */
 public function run()
 {
  // テストユーザーを取得（存在しない場合は作成）
  $buyer = User::firstOrCreate(
   ['email' => 'buyer@example.com'],
   [
    'name' => '購入者',
    'password' => bcrypt('password'),
   ]
  );

  // 既存の商品を取得
  $items = Item::all();

  // 購入済みにする商品のID（最初の3つの商品を購入済みにする）
  $soldItemIds = $items->take(3)->pluck('id');

  foreach ($soldItemIds as $itemId) {
   Purchase::create([
    'user_id' => $buyer->id,
    'item_id' => $itemId,
    'payment_method' => 'credit_card',
    'postcode' => '1234567',
    'address' => '東京都千代田区永田町1-7-1',
    'building_name' => '永田町ビル101',
    'purchased_at' => now()->subDays(rand(1, 30)), // 過去30日以内のランダムな日付
   ]);
  }

  // 追加で購入済みにする商品（ランダムに選択）
  $remainingItems = $items->whereNotIn('id', $soldItemIds);
  $randomSoldItems = $remainingItems->random(min(2, $remainingItems->count()));

  foreach ($randomSoldItems as $item) {
   Purchase::create([
    'user_id' => $buyer->id,
    'item_id' => $item->id,
    'payment_method' => 'credit_card',
    'postcode' => '1234567',
    'address' => '東京都千代田区永田町1-7-1',
    'building_name' => '永田町ビル101',
    'purchased_at' => now()->subDays(rand(1, 30)),
   ]);
  }
 }
}
