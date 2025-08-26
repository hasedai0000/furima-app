<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
  protected $model = Item::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      'user_id' => User::factory(),
      'name' => $this->faker->words(3, true),
      'brand_name' => $this->faker->company(),
      'description' => $this->faker->paragraph(),
      'price' => $this->faker->numberBetween(100, 100000),
      'condition' => $this->faker->randomElement(['poor', 'normal', 'good', 'excellent']),
      'img_url' => $this->faker->imageUrl(640, 480, 'products'),
    ];
  }
}
