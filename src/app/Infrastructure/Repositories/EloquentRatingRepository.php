<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Transaction\Entities\Rating as RatingEntity;
use App\Domain\Transaction\Repositories\RatingRepositoryInterface;
use App\Models\Rating;

class EloquentRatingRepository implements RatingRepositoryInterface
{
  /**
   * 評価を保存
   *
   * @param RatingEntity $rating
   * @return void
   */
  public function save(RatingEntity $rating): void
  {
    $eloquentRating = new Rating();
    $eloquentRating->id = $rating->getId();
    $eloquentRating->transaction_id = $rating->getTransactionId();
    $eloquentRating->rater_id = $rating->getRaterId();
    $eloquentRating->rated_id = $rating->getRatedId();
    $eloquentRating->rating = $rating->getRating();
    $eloquentRating->comment = $rating->getComment();
    $eloquentRating->save();
  }

  /**
   * 取引IDに紐づく評価を取得
   *
   * @param string $transactionId
   * @return array<RatingEntity>
   */
  public function findByTransactionId(string $transactionId): array
  {
    $ratings = Rating::where('transaction_id', $transactionId)->get();

    return $ratings->map(function ($rating) {
      return $this->toEntity($rating);
    })->toArray();
  }

  /**
   * ユーザーIDに紐づく評価を取得（評価された側）
   *
   * @param string $userId
   * @return array<RatingEntity>
   */
  public function findByRatedUserId(string $userId): array
  {
    $ratings = Rating::where('rated_id', $userId)->get();

    return $ratings->map(function ($rating) {
      return $this->toEntity($rating);
    })->toArray();
  }

  /**
   * Eloquentモデルをエンティティに変換
   *
   * @param Rating $rating
   * @return RatingEntity
   */
  private function toEntity(Rating $rating): RatingEntity
  {
    return new RatingEntity(
      $rating->id,
      $rating->transaction_id,
      $rating->rater_id,
      $rating->rated_id,
      $rating->rating,
      $rating->comment,
      $rating->created_at ? new \DateTime($rating->created_at->format('Y-m-d H:i:s')) : null
    );
  }
}
