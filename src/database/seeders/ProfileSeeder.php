<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProfileSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $users = User::all();

    if ($users->isEmpty()) {
      $this->command->warn('No users found. Please run UserSeeder first.');
      return;
    }

    $addressData = [
      [
        'postcode' => '1500001',
        'address' => '東京都渋谷区神宮前1-1-1',
        'building_name' => 'シティマンション101',
      ],
      [
        'postcode' => '5300001',
        'address' => '大阪府大阪市北区梅田2-2-2',
        'building_name' => 'ビジネスタワー502',
      ],
      [
        'postcode' => '4600001',
        'address' => '愛知県名古屋市中区栄3-3-3',
        'building_name' => null,
      ],
      [
        'postcode' => '2200001',
        'address' => '神奈川県横浜市西区みなとみらい4-4-4',
        'building_name' => 'オーシャンビュー203',
      ],
      [
        'postcode' => '8120001',
        'address' => '福岡県福岡市博多区博多駅前5-5-5',
        'building_name' => 'ビジネスプラザ301',
      ],
      [
        'postcode' => '9800001',
        'address' => '宮城県仙台市青葉区中央6-6-6',
        'building_name' => null,
      ],
      [
        'postcode' => '7000001',
        'address' => '広島県広島市中区紙屋町7-7-7',
        'building_name' => 'グリーンハウス402',
      ],
      [
        'postcode' => '0600001',
        'address' => '北海道札幌市中央区大通西8-8-8',
        'building_name' => 'スノーマンション201',
      ],
      [
        'postcode' => '9000001',
        'address' => '沖縄県那覇市首里城9-9-9',
        'building_name' => 'パラダイスヴィラ105',
      ],
      [
        'postcode' => '7600001',
        'address' => '香川県高松市高松町10-10-10',
        'building_name' => null,
      ],
      [
        'postcode' => '1350001',
        'address' => '東京都江東区豊洲11-11-11',
        'building_name' => 'ベイサイドレジデンス304',
      ],
      [
        'postcode' => '5410001',
        'address' => '大阪府大阪市中央区天満橋12-12-12',
        'building_name' => 'リバーサイドハイツ201',
      ],
      [
        'postcode' => '2310001',
        'address' => '神奈川県横浜市中区関内13-13-13',
        'building_name' => 'シーサイドタワー1001',
      ],
      [
        'postcode' => '4640001',
        'address' => '愛知県名古屋市千種区今池14-14-14',
        'building_name' => null,
      ],
      [
        'postcode' => '8130001',
        'address' => '福岡県福岡市東区香椎15-15-15',
        'building_name' => 'ガーデンテラス503',
      ],
    ];

    $profileImages = [
      null, // プロフィール画像なし
      'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200',
      'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200',
      null, // プロフィール画像なし
      'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=200',
      null, // プロフィール画像なし
      'https://images.unsplash.com/photo-1463453091185-61582044d556?w=200',
      null, // プロフィール画像なし
      'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200',
      null, // プロフィール画像なし
      'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=200',
      'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200',
      'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200',
      'https://images.unsplash.com/photo-1499952127939-9bbf5af6c51c?w=200',
      null, // プロフィール画像なし
    ];

    foreach ($users as $index => $user) {
      $addressIndex = $index % count($addressData);
      $imageIndex = $index % count($profileImages);

      $address = $addressData[$addressIndex];

      // 既存のプロフィールをチェック（重複を避けるため）
      $existingProfile = Profile::where('user_id', $user->id)->first();

      if (!$existingProfile) {
        Profile::create([
          'id' => Str::uuid(),
          'user_id' => $user->id,
          'img_url' => $profileImages[$imageIndex],
          'postcode' => $address['postcode'],
          'address' => $address['address'],
          'building_name' => $address['building_name'],
        ]);
      }
    }

    // 作成されたプロフィール数をログに出力
    $profileCount = Profile::count();
    $this->command->info("Created {$profileCount} profiles successfully!");
  }
}
