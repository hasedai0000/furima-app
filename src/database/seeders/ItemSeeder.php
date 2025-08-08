<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
 /**
  * Run the database seeds.
  *
  * @return void
  */
 public function run()
 {
  // テストユーザーを取得（存在しない場合は作成）
  $user = User::firstOrCreate(
   ['email' => 'test@example.com'],
   [
    'name' => 'テストユーザー',
    'password' => bcrypt('password'),
   ]
  );

  $items = [
   [
    'user_id' => $user->id,
    'name' => '腕時計',
    'description' => 'スタイリッシュなデザインのメンズ腕時計',
    'price' => 15000,
    'condition' => '良好',
    'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
   ],
   [
    'user_id' => $user->id,
    'name' => 'HDD',
    'description' => '高速で信頼性の高いハードディスク',
    'price' => 5000,
    'condition' => '目立った傷や汚れなし',
    'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
   ],
   [
    'user_id' => $user->id,
    'name' => '玉ねぎ3束',
    'description' => '新鮮な玉ねぎ3束のセット',
    'price' => 300,
    'condition' => 'やや傷や汚れあり',
    'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
   ],
   [
    'user_id' => $user->id,
    'name' => '革靴',
    'description' => 'クラシックなデザインの革靴',
    'price' => 4000,
    'condition' => '状態が悪い',
    'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
   ],
   [
    'user_id' => $user->id,
    'name' => 'ノートPC',
    'description' => '高性能なノートパソコン',
    'price' => 45000,
    'condition' => '良好',
    'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
   ],
   [
    'user_id' => $user->id,
    'name' => 'マイク',
    'description' => '高音質のレコーディング用マイク',
    'price' => 8000,
    'condition' => '目立った傷や汚れなし',
    'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
   ],
   [
    'user_id' => $user->id,
    'name' => 'ショルダーバッグ',
    'description' => 'おしゃれなショルダーバッグ',
    'price' => 3500,
    'condition' => 'やや傷や汚れあり',
    'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
   ],
   [
    'user_id' => $user->id,
    'name' => 'タンブラー',
    'description' => '使いやすいタンブラー',
    'price' => 500,
    'condition' => '状態が悪い',
    'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
   ],
   [
    'user_id' => $user->id,
    'name' => 'コーヒーミル',
    'description' => '手動のコーヒーミル',
    'price' => 4000,
    'condition' => '良好',
    'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
   ],
   [
    'user_id' => $user->id,
    'name' => 'メイクセット',
    'description' => '便利なメイクアップセット',
    'price' => 2500,
    'condition' => '目立った傷や汚れなし',
    'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
   ],
  ];

  foreach ($items as $itemData) {
   Item::create([
    'user_id' => $user->id,
    'name' => $itemData['name'],
    'description' => $itemData['description'],
    'price' => $itemData['price'],
    'condition' => $itemData['condition'],
    'img_url' => $itemData['img_url'],
   ]);
  }
 }
}
