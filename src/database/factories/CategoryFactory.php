<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
 protected $model = Category::class;

 /**
  * Define the model's default state.
  *
  * @return array
  */
 public function definition()
 {
  return [
   'name' => $this->faker->unique()->randomElement([
    'ファッション',
    '家電・スマホ・カメラ',
    'エンタメ・ホビー',
    'コスメ・香水・美容',
    'スポーツ・レジャー',
    'ハンドメイド',
    'インテリア・住まい・小物',
    '本・音楽・ゲーム',
    'おもちゃ・ホビー・グッズ',
    'レディース',
    'メンズ',
    'ベビー・キッズ',
    'その他'
   ]),
  ];
 }
}
