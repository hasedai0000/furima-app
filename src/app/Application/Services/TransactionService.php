<?php

namespace App\Application\Services;

use App\Application\Services\AuthenticationService;
use App\Domain\Transaction\Entities\Transaction as TransactionEntity;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Mail\TransactionCompletedMail;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TransactionService
{
  private TransactionRepositoryInterface $transactionRepository;
  private AuthenticationService $authService;

  public function __construct(
    TransactionRepositoryInterface $transactionRepository,
    AuthenticationService $authService
  ) {
    $this->transactionRepository = $transactionRepository;
    $this->authService = $authService;
  }

  /**
   * 取引を取得
   *
   * @param string $transactionId
   * @return TransactionEntity|null
   */
  public function getTransaction(string $transactionId): ?TransactionEntity
  {
    $transaction = $this->transactionRepository->findById($transactionId);
    if (!$transaction) {
      return null;
    }

    // 認証チェック：取引に関与しているユーザーのみアクセス可能
    $userId = $this->authService->requireAuthentication();
    if ($transaction->getBuyerId() !== $userId && $transaction->getSellerId() !== $userId) {
      throw new \Exception('Unauthorized');
    }

    return $transaction;
  }

  /**
   * ユーザーが参加している取引一覧を取得
   *
   * @return array<TransactionEntity>
   */
  public function getUserTransactions(): array
  {
    $userId = $this->authService->requireAuthentication();
    return $this->transactionRepository->findByUserId($userId);
  }

  /**
   * 取引を完了にする
   *
   * @param string $transactionId
   * @return TransactionEntity
   */
  public function completeTransaction(string $transactionId): TransactionEntity
  {
    $userId = $this->authService->requireAuthentication();
    $transaction = $this->transactionRepository->findById($transactionId);

    if (!$transaction) {
      throw new \Exception('Transaction not found');
    }

    // 購入者のみ取引を完了にできる
    if ($transaction->getBuyerId() !== $userId) {
      throw new \Exception('Only buyer can complete the transaction');
    }

    // 既に完了している場合はエラー
    if ($transaction->getStatus() === 'completed') {
      throw new \Exception('Transaction is already completed');
    }

    $transaction->setStatus('completed');
    $transaction->setCompletedAt(new \DateTime());
    $this->transactionRepository->update($transaction);

    // 出品者にメールを送信
    $item = Item::find($transaction->getItemId());
    $seller = User::find($transaction->getSellerId());
    $buyer = User::find($transaction->getBuyerId());

    if ($item && $seller && $buyer) {
      Mail::to($seller->email)->send(
        new TransactionCompletedMail(
          $transaction,
          $item->name,
          $buyer->name
        )
      );
    }

    return $transaction;
  }

  /**
   * 商品IDでアクティブな取引があるかチェック
   *
   * @param string $itemId
   * @return bool
   */
  public function hasActiveTransaction(string $itemId): bool
  {
    $transaction = $this->transactionRepository->findActiveByItemId($itemId);
    return $transaction !== null;
  }
}
