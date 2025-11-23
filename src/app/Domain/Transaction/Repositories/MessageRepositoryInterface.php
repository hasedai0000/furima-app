<?php

namespace App\Domain\Transaction\Repositories;

use App\Domain\Transaction\Entities\Message as MessageEntity;

interface MessageRepositoryInterface
{
  /**
   * メッセージを保存する
   *
   * @param MessageEntity $message
   * @return void
   */
  public function save(MessageEntity $message): void;

  /**
   * 取引IDに紐づくメッセージ一覧を取得
   *
   * @param string $transactionId
   * @return array<MessageEntity>
   */
  public function findByTransactionId(string $transactionId): array;

  /**
   * メッセージIDでメッセージを取得
   *
   * @param string $messageId
   * @return MessageEntity|null
   */
  public function findById(string $messageId): ?MessageEntity;

  /**
   * メッセージを更新
   *
   * @param MessageEntity $message
   * @return void
   */
  public function update(MessageEntity $message): void;

  /**
   * メッセージを削除（soft delete）
   *
   * @param string $messageId
   * @return void
   */
  public function delete(string $messageId): void;
}
