<?php

namespace App\Domain\Transaction\Entities;

class Message
{
  private string $id;
  private string $transactionId;
  private string $userId;
  private ?string $content;
  private \DateTime $createdAt;
  private ?\DateTime $updatedAt;
  private ?\DateTime $deletedAt;

  public function __construct(
    string $id,
    string $transactionId,
    string $userId,
    ?string $content = null,
    ?\DateTime $createdAt = null,
    ?\DateTime $updatedAt = null,
    ?\DateTime $deletedAt = null
  ) {
    $this->id = $id;
    $this->transactionId = $transactionId;
    $this->userId = $userId;
    $this->content = $content;
    $this->createdAt = $createdAt ?? new \DateTime();
    $this->updatedAt = $updatedAt;
    $this->deletedAt = $deletedAt;
  }

  public function getId(): string
  {
    return $this->id;
  }

  public function getTransactionId(): string
  {
    return $this->transactionId;
  }

  public function getUserId(): string
  {
    return $this->userId;
  }

  public function getContent(): ?string
  {
    return $this->content;
  }

  public function getCreatedAt(): \DateTime
  {
    return $this->createdAt;
  }

  public function getUpdatedAt(): ?\DateTime
  {
    return $this->updatedAt;
  }

  public function getDeletedAt(): ?\DateTime
  {
    return $this->deletedAt;
  }

  public function setContent(?string $content): void
  {
    $this->content = $content;
  }

  public function setUpdatedAt(?\DateTime $updatedAt): void
  {
    $this->updatedAt = $updatedAt;
  }

  public function setDeletedAt(?\DateTime $deletedAt): void
  {
    $this->deletedAt = $deletedAt;
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'transactionId' => $this->transactionId,
      'userId' => $this->userId,
      'content' => $this->content,
      'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
      'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
      'deletedAt' => $this->deletedAt ? $this->deletedAt->format('Y-m-d H:i:s') : null,
    ];
  }
}
