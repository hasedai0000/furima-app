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

        $items = [
            [
                'name' => '腕時計',
                'brand_name' => 'Rolex',
                'description' => 'スタイリッシュなデザインのメンズ腕時計。防水機能付きで日常使いに最適です。',
                'price' => 15000,
                'condition' => ItemCondition::GOOD,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
            ],
            [
                'name' => 'HDD',
                'brand_name' => '東芝',
                'description' => '高速で信頼性の高いハードディスク。1TB容量でデータ保存に最適です。',
                'price' => 5000,
                'condition' => ItemCondition::NO_DAMAGE,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
            ],
            [
                'name' => '玉ねぎ3束',
                'brand_name' => null,
                'description' => '新鮮な玉ねぎ3束のセット。料理に使いやすいサイズです。',
                'price' => 300,
                'condition' => ItemCondition::SOME_DAMAGE,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
            ],
            [
                'name' => '革靴',
                'brand_name' => 'Clarks',
                'description' => 'クラシックなデザインの本革靴。ビジネスシーンに最適です。',
                'price' => 4000,
                'condition' => ItemCondition::BAD,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
            ],
            [
                'name' => 'ノートPC',
                'brand_name' => null,
                'description' => '高性能なノートパソコン。在宅ワークや学習に最適です。',
                'price' => 45000,
                'condition' => ItemCondition::GOOD,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
            ],
            [
                'name' => 'マイク',
                'brand_name' => 'なし',
                'description' => '高音質のレコーディング用マイク。配信や録音に最適です。',
                'price' => 8000,
                'condition' => ItemCondition::NO_DAMAGE,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
            ],
            [
                'name' => 'ショルダーバッグ',
                'brand_name' => null,
                'description' => 'おしゃれなショルダーバッグ。デイリーユースに最適なサイズです。',
                'price' => 3500,
                'condition' => ItemCondition::SOME_DAMAGE,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
            ],
            [
                'name' => 'タンブラー',
                'brand_name' => 'なし',
                'description' => '保温・保冷機能付きの使いやすいタンブラー。',
                'price' => 500,
                'condition' => ItemCondition::BAD,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
            ],
            [
                'name' => 'コーヒーミル',
                'brand_name' => 'Starbacks',
                'description' => '手動のコーヒーミル。挽きたてのコーヒーをお楽しみください。',
                'price' => 4000,
                'condition' => ItemCondition::GOOD,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
            ],
            [
                'name' => 'メイクセット',
                'brand_name' => null,
                'description' => '便利なメイクアップセット。基本的なアイテムが揃っています。',
                'price' => 2500,
                'condition' => ItemCondition::NO_DAMAGE,
                'img_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
            ],
        ];

        $userArray = $users->toArray();
        shuffle($userArray); // ユーザーをランダムに並び替え

        foreach ($items as $index => $itemData) {
            // アイテムをランダムなユーザーに割り当て
            $randomUser = $userArray[$index % count($userArray)];

            Item::create([
                'id' => Str::uuid(),
                'user_id' => $randomUser['id'],
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
