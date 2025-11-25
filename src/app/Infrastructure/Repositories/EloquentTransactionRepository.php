<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Transaction\Entities\Transaction as TransactionEntity;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Models\Transaction;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
  /**
   * 取引を保存する
   *
   * @param TransactionEntity $transaction
   * @return void
   */
  public function save(TransactionEntity $transaction): void
  {
    $eloquentTransaction = new Transaction();
    $eloquentTransaction->id = $transaction->getId();
    $eloquentTransaction->item_id = $transaction->getItemId();
    $eloquentTransaction->buyer_id = $transaction->getBuyerId();
    $eloquentTransaction->seller_id = $transaction->getSellerId();
    $eloquentTransaction->status = $transaction->getStatus();
    $eloquentTransaction->completed_at = $transaction->getCompletedAt();
    $eloquentTransaction->save();
  }

  /**
   * 取引IDで取引を取得
   *
   * @param string $transactionId
   * @return TransactionEntity|null
   */
  public function findById(string $transactionId): ?TransactionEntity
  {
    $transaction = Transaction::with('item', 'buyer', 'seller')->find($transactionId);
    if (!$transaction) {
      return null;
    }

    return $this->toEntity($transaction);
  }

  /**
   * ユーザーが参加している取引一覧を取得（購入者または出品者）
   *
   * @param string $userId
   * @return array<TransactionEntity>
   */
  public function findByUserId(string $userId): array
  {
    $transactions = Transaction::with('item', 'buyer', 'seller')
      ->where('buyer_id', $userId)
      ->orWhere('seller_id', $userId)
      ->orderBy('updated_at', 'desc')
      ->get();

    return $transactions->map(function ($transaction) {
      return $this->toEntity($transaction);
    })->toArray();
  }

  /**
   * 取引を更新
   *
   * @param TransactionEntity $transaction
   * @return void
   */
  public function update(TransactionEntity $transaction): void
  {
    $eloquentTransaction = Transaction::find($transaction->getId());
    if (!$eloquentTransaction) {
      throw new \Exception('Transaction not found');
    }

    $eloquentTransaction->status = $transaction->getStatus();
    $eloquentTransaction->completed_at = $transaction->getCompletedAt();
    $eloquentTransaction->save();
  }

  /**
   * 商品IDでアクティブな取引を取得
   *
   * @param string $itemId
   * @return TransactionEntity|null
   */
  public function findActiveByItemId(string $itemId): ?TransactionEntity
  {
    $transaction = Transaction::where('item_id', $itemId)
      ->where('status', 'active')
      ->first();

    if (!$transaction) {
      return null;
    }

    return $this->toEntity($transaction);
  }

  /**
   * Eloquentモデルをエンティティに変換
   *
   * @param Transaction $transaction
   * @return TransactionEntity
   */
  private function toEntity(Transaction $transaction): TransactionEntity
  {
    return new TransactionEntity(
      $transaction->id,
      $transaction->item_id,
      $transaction->buyer_id,
      $transaction->seller_id,
      $transaction->status,
      $transaction->completed_at ? new \DateTime($transaction->completed_at->format('Y-m-d H:i:s')) : null
    );
  }
}
