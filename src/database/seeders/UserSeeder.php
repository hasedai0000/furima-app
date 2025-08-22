<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
         [
          'name' => 'テストユーザー',
          'email' => 'test@example.com',
          'password' => 'password',
          'email_verified_at' => now(), // メール認証済み
         ],
         [
          'name' => '田中太郎',
          'email' => 'tanaka@example.com',
          'password' => 'password',
          'email_verified_at' => now(),
         ],
         [
          'name' => '佐藤花子',
          'email' => 'sato@example.com',
          'password' => 'password',
          'email_verified_at' => now(),
         ],
         [
          'name' => '山田次郎',
          'email' => 'yamada@example.com',
          'password' => 'password',
          'email_verified_at' => null, // メール未認証
         ],
         [
          'name' => '鈴木美咲',
          'email' => 'suzuki@example.com',
          'password' => 'password',
          'email_verified_at' => now(),
         ],
         [
          'name' => '高橋健太',
          'email' => 'takahashi@example.com',
          'password' => 'password',
          'email_verified_at' => now(),
         ],
         [
          'name' => '伊藤愛',
          'email' => 'ito@example.com',
          'password' => 'password',
          'email_verified_at' => null, // メール未認証
         ],
         [
          'name' => '渡辺翔太',
          'email' => 'watanabe@example.com',
          'password' => 'password',
          'email_verified_at' => now(),
         ],
         [
          'name' => '中村優子',
          'email' => 'nakamura@example.com',
          'password' => 'password',
          'email_verified_at' => now(),
         ],
         [
          'name' => '小林大輔',
          'email' => 'kobayashi@example.com',
          'password' => 'password',
          'email_verified_at' => now(),
         ],
        ];

        foreach ($users as $userData) {
            // 既存のユーザーをチェック（重複を避けるため）
            $existingUser = User::where('email', $userData['email'])->first();

            if (! $existingUser) {
                User::create([
                 'name' => $userData['name'],
                 'email' => $userData['email'],
                 'password' => Hash::make($userData['password']),
                 'email_verified_at' => $userData['email_verified_at'],
                ]);
            }
        }

        // 作成されたユーザー数をログに出力
        $userCount = User::count();
        $this->command->info("Created {$userCount} users successfully!");
    }
}
