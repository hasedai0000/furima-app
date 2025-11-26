<?php

namespace Database\Factories;

use App\Models\Rating;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingFactory extends Factory
{
  protected $model = Rating::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      'transaction_id' => Transaction::factory(),
      'rater_id' => User::factory(),
      'rated_id' => User::factory(),
      'rating' => $this->faker->numberBetween(1, 5),
      'comment' => $this->faker->optional()->sentence(),
    ];
  }
}
