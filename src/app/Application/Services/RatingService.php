<?php

namespace App\Application\Services;

use App\Application\Services\AuthenticationService;
use App\Domain\Transaction\Entities\Rating as RatingEntity;
use App\Domain\Transaction\Repositories\RatingRepositoryInterface;
use Illuminate\Support\Str;

class RatingService
{
  private RatingRepositoryInterface $ratingRepository;
  private AuthenticationService $authService;

  public function __construct(
    RatingRepositoryInterface $ratingRepository,
    AuthenticationService $authService
  ) {
    $this->ratingRepository = $ratingRepository;
    $this->authService = $authService;
  }

  /**
   * 評価を作成
   *
   * @param string $transactionId
   * @param string $ratedId
   * @param int $rating
   * @param string|null $comment
   * @return RatingEntity
   */
  public function createRating(string $transactionId, string $ratedId, int $rating, ?string $comment = null): RatingEntity
  {
    $raterId = $this->authService->requireAuthentication();

    // 評価は1-5の範囲
    if ($rating < 1 || $rating > 5) {
      throw new \Exception('Rating must be between 1 and 5');
    }

    $ratingEntity = new RatingEntity(
      Str::uuid()->toString(),
      $transactionId,
      $raterId,
      $ratedId,
      $rating,
      $comment
    );

    $this->ratingRepository->save($ratingEntity);

    return $ratingEntity;
  }

  /**
   * 取引IDに紐づく評価を取得
   *
   * @param string $transactionId
   * @return array<RatingEntity>
   */
  public function getRatingsByTransactionId(string $transactionId): array
  {
    return $this->ratingRepository->findByTransactionId($transactionId);
  }

  /**
   * ユーザーの評価平均を取得
   *
   * @param string $userId
   * @return float|null
   */
  public function getAverageRating(string $userId): ?float
  {
    $ratings = $this->ratingRepository->findByRatedUserId($userId);

    if (count($ratings) === 0) {
      return null;
    }

    $total = 0;
    foreach ($ratings as $rating) {
      $total += $rating->getRating();
    }

    $average = $total / count($ratings);
    return round($average, 0); // 四捨五入
  }

  /**
   * 取引で既に評価済みかチェック
   *
   * @param string $transactionId
   * @param string $userId
   * @return bool
   */
  public function hasRated(string $transactionId, string $userId): bool
  {
    $ratings = $this->ratingRepository->findByTransactionId($transactionId);
    foreach ($ratings as $rating) {
      if ($rating->getRaterId() === $userId) {
        return true;
      }
    }
    return false;
  }
}
