<?php

namespace App\Domain\Transaction\Entities;

class Rating
{
  private string $id;
  private string $transactionId;
  private string $raterId;
  private string $ratedId;
  private int $rating;
  private ?string $comment;
  private \DateTime $createdAt;

  public function __construct(
    string $id,
    string $transactionId,
    string $raterId,
    string $ratedId,
    int $rating,
    ?string $comment = null,
    ?\DateTime $createdAt = null
  ) {
    $this->id = $id;
    $this->transactionId = $transactionId;
    $this->raterId = $raterId;
    $this->ratedId = $ratedId;
    $this->rating = $rating;
    $this->comment = $comment;
    $this->createdAt = $createdAt ?? new \DateTime();
  }

  public function getId(): string
  {
    return $this->id;
  }

  public function getTransactionId(): string
  {
    return $this->transactionId;
  }

  public function getRaterId(): string
  {
    return $this->raterId;
  }

  public function getRatedId(): string
  {
    return $this->ratedId;
  }

  public function getRating(): int
  {
    return $this->rating;
  }

  public function getComment(): ?string
  {
    return $this->comment;
  }

  public function getCreatedAt(): \DateTime
  {
    return $this->createdAt;
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'transactionId' => $this->transactionId,
      'raterId' => $this->raterId,
      'ratedId' => $this->ratedId,
      'rating' => $this->rating,
      'comment' => $this->comment,
      'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
    ];
  }
}
