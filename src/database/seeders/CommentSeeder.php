<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommentSeeder extends Seeder
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

  // コメントの例文
  $commentTemplates = [
   'こちらの商品はまだ購入可能でしょうか？',
   '送料込みの価格ですか？',
   '状態はどのような感じでしょうか？',
   '購入を検討しているのですが、値下げは可能でしょうか？',
   '写真以外の角度の画像をいただけますか？',
   'サイズや寸法を教えてください。',
   '使用頻度はどれくらいでしたか？',
   '付属品はありますか？',
   '配送方法について教えてください。',
   'いつ頃購入されたものですか？',
   '他のサイトでも出品されていますか？',
   '商品の詳細について質問があります。',
   '動作確認は取れていますか？',
   '保証書や説明書はありますか？',
   '梱包は丁寧にしていただけますか？',
   '専用ページを作っていただけますか？',
   '即購入させていただきます。',
   'コメント失礼いたします。',
   '購入希望です。',
   'この商品を探していました！',
   '綺麗な状態ですね。',
   '素敵な商品だと思います。',
   'お取り置きは可能でしょうか？',
   '別の色はありませんか？',
   '類似商品の出品予定はありますか？',
  ];

  $comments = [];

  // 全アイテムの30%程度にコメントを付ける
  $itemsWithComments = $items->random((int) ($items->count() * 0.3));

  foreach ($itemsWithComments as $item) {
   // 自分の商品以外にコメントできるユーザー
   $availableCommenters = $users->where('id', '!=', $item->user_id);

   if ($availableCommenters->isEmpty()) {
    continue;
   }

   // 1つのアイテムに1〜4個のコメント
   $commentCount = rand(1, 4);
   $commenters = $availableCommenters->random(min($commentCount, $availableCommenters->count()));

   foreach ($commenters as $commenter) {
    $commentContent = $commentTemplates[array_rand($commentTemplates)];

    // ランダムに詳細を追加
    if (rand(1, 100) <= 30) { // 30%の確率で詳細を追加
     $additionalComments = [
      'よろしくお願いいたします。',
      'ご検討ください。',
      'お忙しい中恐れ入ります。',
      'お手数ですがよろしくお願いします。',
      'ご返答お待ちしております。',
     ];
     $commentContent .= ' ' . $additionalComments[array_rand($additionalComments)];
    }

    $createdAt = now()->subDays(rand(0, 20)); // 過去20日以内

    $comments[] = [
     'id' => Str::uuid(),
     'user_id' => $commenter->id,
     'item_id' => $item->id,
     'content' => $commentContent,
     'created_at' => $createdAt,
     'updated_at' => $createdAt,
    ];
   }
  }

  // 人気商品（いいねの多い商品）により多くのコメントを追加
  $popularItems = $items->sortByDesc(function ($item) {
   return $item->likes()->count();
  })->take(3);

  foreach ($popularItems as $item) {
   $availableCommenters = $users->where('id', '!=', $item->user_id);

   if ($availableCommenters->isEmpty()) {
    continue;
   }

   // 人気商品には2〜6個の追加コメント
   $additionalCommentCount = rand(2, 6);
   $additionalCommenters = $availableCommenters->random(min($additionalCommentCount, $availableCommenters->count()));

   foreach ($additionalCommenters as $commenter) {
    // 既にコメントしているかチェック
    $alreadyCommented = collect($comments)->contains(function ($comment) use ($commenter, $item) {
     return $comment['user_id'] === $commenter->id && $comment['item_id'] === $item->id;
    });

    if (!$alreadyCommented) {
     $commentContent = $commentTemplates[array_rand($commentTemplates)];
     $createdAt = now()->subDays(rand(0, 10)); // より最近

     $comments[] = [
      'id' => Str::uuid(),
      'user_id' => $commenter->id,
      'item_id' => $item->id,
      'content' => $commentContent,
      'created_at' => $createdAt,
      'updated_at' => $createdAt,
     ];
    }
   }
  }

  // コメントを作成
  foreach ($comments as $commentData) {
   Comment::create($commentData);
  }

  // 作成されたコメント数をログに出力
  $commentCount = Comment::count();
  $this->command->info("Created {$commentCount} comments successfully!");
 }
}
