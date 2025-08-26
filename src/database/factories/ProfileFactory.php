<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Profile::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      'user_id' => User::factory(),
      'img_url' => 'storage/images/default-profile.png',
      'postcode' => $this->faker->numerify('###-####'), // 日本の郵便番号形式（XXX-XXXX）
      'address' => '東京都渋谷区神宮前' . $this->faker->numberBetween(1, 6) . '-' . $this->faker->numberBetween(1, 30) . '-' . $this->faker->numberBetween(1, 20),
      'building_name' => $this->faker->optional()->secondaryAddress(),
    ];
  }

  /**
   * プロフィール画像なしの状態
   *
   * @return \Illuminate\Database\Eloquent\Factories\Factory
   */
  public function withoutImage()
  {
    return $this->state(function (array $attributes) {
      return [
        'img_url' => null,
      ];
    });
  }

  /**
   * 建物名なしの状態
   *
   * @return \Illuminate\Database\Eloquent\Factories\Factory
   */
  public function withoutBuildingName()
  {
    return $this->state(function (array $attributes) {
      return [
        'building_name' => null,
      ];
    });
  }
}
