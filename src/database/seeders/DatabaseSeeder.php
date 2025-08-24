<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        $this->call([
            UserSeeder::class,           // 1. ユーザーを最初に作成
            ProfileSeeder::class,        // 2. ユーザープロファイルを作成
            CategorySeeder::class,       // 3. カテゴリーを作成
            ItemSeeder::class,          // 4. アイテムを作成
            ItemCategorySeeder::class,  // 5. アイテムとカテゴリーの関連付け
            PurchaseSeeder::class,      // 6. 購入データを作成
            CommentSeeder::class,       // 8. コメントデータを作成
        ]);

        $this->command->info('All seeders have been executed successfully!');
        $this->command->info('Your Furima application now has comprehensive test data.');
    }
}
