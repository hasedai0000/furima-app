<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
                'id' => Str::uuid(),
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => 'password',
                'email_verified_at' => now(), // メール認証済み
            ],
            [
                'id' => Str::uuid(),
                'name' => '田中太郎',
                'email' => 'tanaka@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => '佐藤花子',
                'email' => 'sato@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => '山田次郎',
                'email' => 'yamada@example.com',
                'password' => 'password',
                'email_verified_at' => null, // メール未認証
            ],
            [
                'id' => Str::uuid(),
                'name' => '鈴木美咲',
                'email' => 'suzuki@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => '高橋健太',
                'email' => 'takahashi@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => '伊藤愛',
                'email' => 'ito@example.com',
                'password' => 'password',
                'email_verified_at' => null, // メール未認証
            ],
            [
                'id' => Str::uuid(),
                'name' => '渡辺翔太',
                'email' => 'watanabe@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => '中村優子',
                'email' => 'nakamura@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => '小林大輔',
                'email' => 'kobayashi@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => '加藤結衣',
                'email' => 'kato@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => '森田浩司',
                'email' => 'morita@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => '松本あかり',
                'email' => 'matsumoto@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => '橋本拓也',
                'email' => 'hashimoto@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => '清水恵子',
                'email' => 'shimizu@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            // 既存のユーザーをチェック（重複を避けるため）
            $existingUser = User::where('email', $userData['email'])->first();

            if (!$existingUser) {
                User::create([
                    'id' => $userData['id'],
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
