<?php

namespace App\Domain\Transaction\Repositories;

use App\Domain\Transaction\Entities\Transaction as TransactionEntity;

interface TransactionRepositoryInterface
{
  public function save(TransactionEntity $transaction): void;

  /**
   * 取引IDで取引を取得
   *
   * @param string $transactionId
   * @return TransactionEntity|null
   */
  public function findById(string $transactionId): ?TransactionEntity;

  /**
   * ユーザーが参加している取引一覧を取得（購入者または出品者）
   *
   * @param string $userId
   * @return array<TransactionEntity>
   */
  public function findByUserId(string $userId): array;

  /**
   * 取引を更新
   *
   * @param TransactionEntity $transaction
   * @return void
   */
  public function update(TransactionEntity $transaction): void;
}
