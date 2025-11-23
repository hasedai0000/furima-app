<?php

namespace App\Domain\Transaction\Entities;

class Transaction
{
  private string $id;
  private string $itemId;
  private string $buyerId;
  private string $sellerId;
  private string $status;
  private ?\DateTime $completedAt;

  public function __construct(
    string $id,
    string $itemId,
    string $buyerId,
    string $sellerId,
    string $status = 'active',
    ?\DateTime $completedAt = null
  ) {
    $this->id = $id;
    $this->itemId = $itemId;
    $this->buyerId = $buyerId;
    $this->sellerId = $sellerId;
    $this->status = $status;
    $this->completedAt = $completedAt;
  }

  public function getId(): string
  {
    return $this->id;
  }

  public function getItemId(): string
  {
    return $this->itemId;
  }

  public function getBuyerId(): string
  {
    return $this->buyerId;
  }

  public function getSellerId(): string
  {
    return $this->sellerId;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function getCompletedAt(): ?\DateTime
  {
    return $this->completedAt;
  }

  public function setStatus(string $status): void
  {
    $this->status = $status;
  }

  public function setCompletedAt(?\DateTime $completedAt): void
  {
    $this->completedAt = $completedAt;
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'itemId' => $this->itemId,
      'buyerId' => $this->buyerId,
      'sellerId' => $this->sellerId,
      'status' => $this->status,
      'completedAt' => $this->completedAt ? $this->completedAt->format('Y-m-d H:i:s') : null,
    ];
  }
}
