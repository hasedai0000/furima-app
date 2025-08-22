<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
         'ファッション',
         '家電',
         'インテリア',
         'レディース',
         'メンズ',
         'コスメ',
         '本',
         'ゲーム',
         'スポーツ',
         'キッチン',
         'ハンドメイド',
         'アクセサリー',
         'おもちゃ',
         'ベビー・キッズ',
        ];

        foreach ($categories as $categoryName) {
            Category::create([
             'name' => $categoryName,
            ]);
        }
    }
}
