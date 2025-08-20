<?php

namespace App\Domain\Purchase\Entities;

use App\Domain\Purchase\ValueObjects\PurchasePostCode;

class Lists
{
  private string $id;
  private string $userId;
  private string $itemId;

  public function __construct(
    string $id,
    string $userId,
    string $itemId,
  ) {
    $this->id = $id;
    $this->userId = $userId;
    $this->itemId = $itemId;
  }

  public function getId(): string
  {
    return $this->id;
  }

  public function getUserId(): string
  {
    return $this->userId;
  }

  public function getItemId(): string
  {
    return $this->itemId;
  }

  public function setItemId(string $itemId): void
  {
    $this->itemId = $itemId;
  }

  public function setUserId(string $userId): void
  {
    $this->userId = $userId;
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'userId' => $this->userId,
      'itemId' => $this->itemId
    ];
  }
}
