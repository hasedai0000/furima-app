<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Domain\Item\ValueObjects\ItemCondition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $categories = Category::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        // ダミーデータ用の特定ユーザーを取得
        $seller1 = User::where('email', 'seller1@dummy.com')->first();
        $seller2 = User::where('email', 'seller2@dummy.com')->first();

        $items = [
            // CO01～CO05: seller1@dummy.com が出品
            [
                'user_id' => $seller1 ? $seller1->id : null,
                'name' => '腕時計',
                'brand_name' => null,
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'price' => 15000,
                'condition' => ItemCondition::GOOD,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
            ],
            [
                'user_id' => $seller1 ? $seller1->id : null,
                'name' => 'HDD',
                'brand_name' => null,
                'description' => '高速で信頼性の高いハードディスク',
                'price' => 5000,
                'condition' => ItemCondition::NO_DAMAGE,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
            ],
            [
                'user_id' => $seller1 ? $seller1->id : null,
                'name' => '玉ねぎ3束',
                'brand_name' => null,
                'description' => '新鮮な玉ねぎ3束のセット',
                'price' => 300,
                'condition' => ItemCondition::SOME_DAMAGE,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
            ],
            [
                'user_id' => $seller1 ? $seller1->id : null,
                'name' => '革靴',
                'brand_name' => null,
                'description' => 'クラシックなデザインの革靴',
                'price' => 4000,
                'condition' => ItemCondition::BAD,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
            ],
            [
                'user_id' => $seller1 ? $seller1->id : null,
                'name' => 'ノートPC',
                'brand_name' => null,
                'description' => '高性能なノートパソコン',
                'price' => 45000,
                'condition' => ItemCondition::GOOD,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
            ],
            // CO06～CO10: seller2@dummy.com が出品
            [
                'user_id' => $seller2 ? $seller2->id : null,
                'name' => 'マイク',
                'brand_name' => null,
                'description' => '高音質のレコーディング用マイク',
                'price' => 8000,
                'condition' => ItemCondition::NO_DAMAGE,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
            ],
            [
                'user_id' => $seller2 ? $seller2->id : null,
                'name' => 'ショルダーバッグ',
                'brand_name' => null,
                'description' => 'おしゃれなショルダーバッグ',
                'price' => 3500,
                'condition' => ItemCondition::SOME_DAMAGE,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
            ],
            [
                'user_id' => $seller2 ? $seller2->id : null,
                'name' => 'タンブラー',
                'brand_name' => null,
                'description' => '使いやすいタンブラー',
                'price' => 500,
                'condition' => ItemCondition::BAD,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
            ],
            [
                'user_id' => $seller2 ? $seller2->id : null,
                'name' => 'コーヒーミル',
                'brand_name' => null,
                'description' => '手動のコーヒーミル',
                'price' => 4000,
                'condition' => ItemCondition::GOOD,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
            ],
            [
                'user_id' => $seller2 ? $seller2->id : null,
                'name' => 'メイクセット',
                'brand_name' => null,
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'condition' => ItemCondition::NO_DAMAGE,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%97%E3%82%BB%E3%83%83%E3%83%88.jpg',
            ],
        ];

        // ダミーユーザーが存在しない場合は、ランダムなユーザーに割り当て
        $userArray = $users->toArray();
        shuffle($userArray);

        foreach ($items as $index => $itemData) {
            // ダミーユーザーが存在しない場合は、ランダムなユーザーに割り当て
            $userId = $itemData['user_id'] ?? $userArray[$index % count($userArray)]['id'];

            Item::create([
                'id' => Str::uuid(),
                'user_id' => $userId,
                'name' => $itemData['name'],
                'brand_name' => $itemData['brand_name'],
                'description' => $itemData['description'],
                'price' => $itemData['price'],
                'condition' => $itemData['condition'],
                'img_url' => $itemData['img_url'],
            ]);
        }

        // 作成されたアイテム数をログに出力
        $itemCount = Item::count();
        $this->command->info("Created {$itemCount} items successfully!");
    }
}
