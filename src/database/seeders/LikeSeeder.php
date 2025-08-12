<?php

namespace Database\Seeders;

use App\Models\Like;
use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Seeder;

class LikeSeeder extends Seeder
{
 /**
  * Run the database seeds.
  *
  * @return void
  */
 public function run()
 {
  // テストユーザーを取得
  $user = User::where('email', 'test@example.com')->first();

  if (!$user) {
   // ユーザーが存在しない場合は作成
   $user = User::create([
    'name' => 'テストユーザー',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
   ]);
  }

  // アイテムを取得
  $items = Item::all();

  if ($items->isEmpty()) {
   // アイテムが存在しない場合は作成
   $this->call(ItemSeeder::class);
   $items = Item::all();
  }

  // いくつかのアイテムにいいねを追加
  $likeData = [
   [
    'user_id' => $user->id,
    'item_id' => $items->first()->id,
   ],
   [
    'user_id' => $user->id,
    'item_id' => $items->skip(1)->first()->id,
   ],
   [
    'user_id' => $user->id,
    'item_id' => $items->skip(2)->first()->id,
   ],
  ];

  foreach ($likeData as $like) {
   // 重複を避けるため、既存のいいねをチェック
   $existingLike = Like::where('user_id', $like['user_id'])
    ->where('item_id', $like['item_id'])
    ->first();

   if (!$existingLike) {
    Like::create($like);
   }
  }

  // 追加のテストデータとして、ランダムにいくつかのアイテムにいいねを追加
  $randomItems = $items->random(min(3, $items->count()));

  foreach ($randomItems as $item) {
   $existingLike = Like::where('user_id', $user->id)
    ->where('item_id', $item->id)
    ->first();

   if (!$existingLike) {
    Like::create([
     'user_id' => $user->id,
     'item_id' => $item->id,
    ]);
   }
  }
 }
}
