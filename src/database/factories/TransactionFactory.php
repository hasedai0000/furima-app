<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
  protected $model = Transaction::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      'item_id' => Item::factory(),
      'buyer_id' => User::factory(),
      'seller_id' => User::factory(),
      'status' => 'active',
      'completed_at' => null,
    ];
  }

  /**
   * Indicate that the transaction is completed.
   *
   * @return \Illuminate\Database\Eloquent\Factories\Factory
   */
  public function completed()
  {
    return $this->state(function (array $attributes) {
      return [
        'status' => 'completed',
        'completed_at' => now(),
      ];
    });
  }
}
