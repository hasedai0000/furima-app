<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $items = Item::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        if ($items->isEmpty()) {
            $this->command->warn('No items found. Please run ItemSeeder first.');
            return;
        }

        // 支払い方法のオプション
        $paymentMethods = ['credit_card', 'convenience_store', 'bank_transfer'];

        // 住所データ
        $addresses = [
            [
                'postcode' => '1000001',
                'address' => '東京都千代田区千代田1-1',
                'building_name' => 'パレスサイドビル203',
            ],
            [
                'postcode' => '5300001',
                'address' => '大阪府大阪市北区梅田3-1-3',
                'building_name' => 'ノースゲートビルディング1505',
            ],
            [
                'postcode' => '2310001',
                'address' => '神奈川県横浜市中区日本大通1',
                'building_name' => null,
            ],
            [
                'postcode' => '4600001',
                'address' => '愛知県名古屋市中区三の丸3-1-2',
                'building_name' => 'セントラルタワーズ802',
            ],
            [
                'postcode' => '8120001',
                'address' => '福岡県福岡市博多区博多駅中央街2-1',
                'building_name' => 'デパートメントビル412',
            ],
            [
                'postcode' => '9800001',
                'address' => '宮城県仙台市青葉区中央1-3-1',
                'building_name' => 'アエルビル1201',
            ],
            [
                'postcode' => '7000001',
                'address' => '広島県広島市中区基町5-44',
                'building_name' => null,
            ],
            [
                'postcode' => '0600001',
                'address' => '北海道札幌市中央区北1条西2-1',
                'building_name' => '札幌時計台ビル301',
            ],
        ];

        // 商品の30%程度を購入済みにする
        $totalItems = $items->count();
        $purchaseCount = (int) ($totalItems * 0.3);
        $itemsToSell = $items->random($purchaseCount);

        $purchases = [];

        foreach ($itemsToSell as $item) {
            // 商品の出品者以外のユーザーをランダムに選択
            $availableBuyers = $users->where('id', '!=', $item->user_id);

            if ($availableBuyers->isEmpty()) {
                continue; // スキップ
            }

            $buyer = $availableBuyers->random();
            $addressData = $addresses[array_rand($addresses)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

            // 購入日は過去1〜60日以内のランダムな日付
            $daysAgo = rand(1, 60);
            $purchasedAt = now()->subDays($daysAgo);

            $purchases[] = [
                'id' => Str::uuid(),
                'user_id' => $buyer->id,
                'item_id' => $item->id,
                'payment_method' => $paymentMethod,
                'postcode' => $addressData['postcode'],
                'address' => $addressData['address'],
                'building_name' => $addressData['building_name'] ?? '',
                'purchased_at' => $purchasedAt,
                'created_at' => $purchasedAt,
                'updated_at' => $purchasedAt,
            ];
        }

        // 重複チェックして作成
        foreach ($purchases as $purchaseData) {
            $existingPurchase = Purchase::where('item_id', $purchaseData['item_id'])->first();

            if (!$existingPurchase) {
                Purchase::create($purchaseData);
            }
        }

        // 作成された購入数をログに出力
        $purchaseCount = Purchase::count();
        $this->command->info("Created {$purchaseCount} purchases successfully!");
    }
}
