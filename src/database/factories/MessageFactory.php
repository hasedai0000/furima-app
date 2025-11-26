<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
  protected $model = Message::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      'transaction_id' => Transaction::factory(),
      'user_id' => User::factory(),
      'content' => $this->faker->sentence(),
    ];
  }
}
