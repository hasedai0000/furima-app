<?php

namespace App\Domain\Transaction\Repositories;

use App\Domain\Transaction\Entities\Rating as RatingEntity;

interface RatingRepositoryInterface
{
  /**
   * 評価を保存
   *
   * @param RatingEntity $rating
   * @return void
   */
  public function save(RatingEntity $rating): void;

  /**
   * 取引IDに紐づく評価を取得
   *
   * @param string $transactionId
   * @return array<RatingEntity>
   */
  public function findByTransactionId(string $transactionId): array;

  /**
   * ユーザーIDに紐づく評価を取得（評価された側）
   *
   * @param string $userId
   * @return array<RatingEntity>
   */
  public function findByRatedUserId(string $userId): array;
}
