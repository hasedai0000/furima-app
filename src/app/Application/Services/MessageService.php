<?php

namespace App\Application\Services;

use App\Application\Contracts\FileUploadServiceInterface;
use App\Application\Services\AuthenticationService;
use App\Domain\Transaction\Entities\Message as MessageEntity;
use App\Domain\Transaction\Repositories\MessageRepositoryInterface;
use App\Models\MessageImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MessageService
{
  private MessageRepositoryInterface $messageRepository;
  private AuthenticationService $authService;
  private FileUploadServiceInterface $fileUploadService;

  public function __construct(
    MessageRepositoryInterface $messageRepository,
    AuthenticationService $authService,
    FileUploadServiceInterface $fileUploadService
  ) {
    $this->messageRepository = $messageRepository;
    $this->authService = $authService;
    $this->fileUploadService = $fileUploadService;
  }

  /**
   * メッセージを送信する
   *
   * @param string $transactionId
   * @param string|null $content
   * @param array<UploadedFile>|null $images
   * @return MessageEntity
   */
  public function sendMessage(string $transactionId, ?string $content = null, ?array $images = null): MessageEntity
  {
    $userId = $this->authService->requireAuthentication();

    $message = new MessageEntity(
      Str::uuid()->toString(),
      $transactionId,
      $userId,
      $content
    );

    $this->messageRepository->save($message);

    // 画像をアップロード
    if ($images && count($images) > 0) {
      foreach ($images as $image) {
        if ($image && $image->isValid()) {
          $imageUrl = $this->fileUploadService->upload($image);
          $messageImage = new MessageImage();
          $messageImage->id = Str::uuid()->toString();
          $messageImage->message_id = $message->getId();
          $messageImage->image_url = $imageUrl;
          $messageImage->save();
        }
      }
    }

    return $message;
  }

  /**
   * 取引IDに紐づくメッセージ一覧を取得
   *
   * @param string $transactionId
   * @return array<MessageEntity>
   */
  public function getMessagesByTransactionId(string $transactionId): array
  {
    return $this->messageRepository->findByTransactionId($transactionId);
  }

  /**
   * メッセージを取得
   *
   * @param string $messageId
   * @return MessageEntity|null
   */
  public function getMessage(string $messageId): ?MessageEntity
  {
    return $this->messageRepository->findById($messageId);
  }

  /**
   * メッセージを更新
   *
   * @param string $messageId
   * @param string|null $content
   * @return MessageEntity
   */
  public function updateMessage(string $messageId, ?string $content = null): MessageEntity
  {
    $userId = $this->authService->requireAuthentication();
    $message = $this->messageRepository->findById($messageId);

    if (!$message) {
      throw new \Exception('Message not found');
    }

    // メッセージの所有者でない場合はエラー
    if ($message->getUserId() !== $userId) {
      throw new \Exception('Unauthorized');
    }

    $message->setContent($content);
    $message->setUpdatedAt(new \DateTime());
    $this->messageRepository->update($message);

    return $message;
  }

  /**
   * メッセージを削除
   *
   * @param string $messageId
   * @return void
   */
  public function deleteMessage(string $messageId): void
  {
    $userId = $this->authService->requireAuthentication();
    $message = $this->messageRepository->findById($messageId);

    if (!$message) {
      throw new \Exception('Message not found');
    }

    // メッセージの所有者でない場合はエラー
    if ($message->getUserId() !== $userId) {
      throw new \Exception('Unauthorized');
    }

    $this->messageRepository->delete($messageId);
  }

  /**
   * 取引の未読メッセージを既読にする
   *
   * @param string $transactionId
   * @param string $userId
   * @return void
   */
  public function markMessagesAsRead(string $transactionId, string $userId): void
  {
    $messages = $this->messageRepository->findByTransactionId($transactionId);

    foreach ($messages as $message) {
      // 自分のメッセージはスキップ
      if ($message->getUserId() === $userId) {
        continue;
      }

      // 既に既読かチェック
      $read = \App\Models\MessageRead::where('message_id', $message->getId())
        ->where('user_id', $userId)
        ->exists();

      if (!$read) {
        // 既読レコードを作成
        $messageRead = new \App\Models\MessageRead();
        $messageRead->id = \Illuminate\Support\Str::uuid()->toString();
        $messageRead->message_id = $message->getId();
        $messageRead->user_id = $userId;
        $messageRead->read_at = now();
        $messageRead->save();
      }
    }
  }
}
