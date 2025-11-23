<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Transaction\Entities\Message as MessageEntity;
use App\Domain\Transaction\Repositories\MessageRepositoryInterface;
use App\Models\Message;

class EloquentMessageRepository implements MessageRepositoryInterface
{
  /**
   * メッセージを保存する
   *
   * @param MessageEntity $message
   * @return void
   */
  public function save(MessageEntity $message): void
  {
    $eloquentMessage = new Message();
    $eloquentMessage->id = $message->getId();
    $eloquentMessage->transaction_id = $message->getTransactionId();
    $eloquentMessage->user_id = $message->getUserId();
    $eloquentMessage->content = $message->getContent();
    $eloquentMessage->save();
  }

  /**
   * 取引IDに紐づくメッセージ一覧を取得
   *
   * @param string $transactionId
   * @return array<MessageEntity>
   */
  public function findByTransactionId(string $transactionId): array
  {
    $messages = Message::where('transaction_id', $transactionId)
      ->orderBy('created_at', 'asc')
      ->get();

    return $messages->map(function ($message) {
      return $this->toEntity($message);
    })->toArray();
  }

  /**
   * メッセージIDでメッセージを取得
   *
   * @param string $messageId
   * @return MessageEntity|null
   */
  public function findById(string $messageId): ?MessageEntity
  {
    $message = Message::find($messageId);
    if (!$message) {
      return null;
    }

    return $this->toEntity($message);
  }

  /**
   * メッセージを更新
   *
   * @param MessageEntity $message
   * @return void
   */
  public function update(MessageEntity $message): void
  {
    $eloquentMessage = Message::find($message->getId());
    if (!$eloquentMessage) {
      throw new \Exception('Message not found');
    }

    $eloquentMessage->content = $message->getContent();
    $eloquentMessage->save();
  }

  /**
   * メッセージを削除（soft delete）
   *
   * @param string $messageId
   * @return void
   */
  public function delete(string $messageId): void
  {
    $message = Message::find($messageId);
    if ($message) {
      $message->delete();
    }
  }

  /**
   * Eloquentモデルをエンティティに変換
   *
   * @param Message $message
   * @return MessageEntity
   */
  private function toEntity(Message $message): MessageEntity
  {
    return new MessageEntity(
      $message->id,
      $message->transaction_id,
      $message->user_id,
      $message->content,
      $message->created_at ? new \DateTime($message->created_at->format('Y-m-d H:i:s')) : null,
      $message->updated_at ? new \DateTime($message->updated_at->format('Y-m-d H:i:s')) : null,
      $message->deleted_at ? new \DateTime($message->deleted_at->format('Y-m-d H:i:s')) : null
    );
  }
}
