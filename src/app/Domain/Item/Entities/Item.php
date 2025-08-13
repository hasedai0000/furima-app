<?php

namespace App\Domain\Item\Entities;

use App\Domain\Item\ValueObjects\ItemImgUrl;
use App\Domain\Item\ValueObjects\ItemName;
use App\Domain\Item\ValueObjects\ItemPrice;

class Item
{
  private string $id;
  private string $userId;
  private string $name;
  private string $description;
  private int $price;
  private string $condition;
  private ItemImgUrl $imgUrl;
  private array $categories = [];

  public function __construct(
    string $id,
    string $userId,
    string $name,
    string $description,
    int $price,
    string $condition,
    ItemImgUrl $imgUrl,
    array $categories
  ) {
    $this->id = $id;
    $this->userId = $userId;
    $this->name = $name;
    $this->description = $description;
    $this->price = $price;
    $this->condition = $condition;
    $this->imgUrl = $imgUrl;
    $this->categories = $categories;
  }

  public function getId(): string
  {
    return $this->id;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function getPrice(): int
  {
    return $this->price;
  }

  public function getCondition(): string
  {
    return $this->condition;
  }

  public function getImgUrl(): ItemImgUrl
  {
    return $this->imgUrl;
  }

  public function getUserId(): string
  {
    return $this->userId;
  }

  public function setUserId(string $userId): void
  {
    $this->userId = $userId;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function setDescription(string $description): void
  {
    $this->description = $description;
  }

  public function setPrice(int $price): void
  {
    $this->price = $price;
  }

  public function setCondition(string $condition): void
  {
    $this->condition = $condition;
  }

  public function setImgUrl(ItemImgUrl $imgUrl): void
  {
    $this->imgUrl = $imgUrl;
  }

  public function getCategories(): array
  {
    return $this->categories;
  }

  public function setCategories(array $categories): void
  {
    $this->categories = $categories;
  }

  /**
   * エンティティを配列に変換する
   *
   * @return array
   */
  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'userId' => $this->userId,
      'name' => $this->name,
      'description' => $this->description,
      'price' => $this->price,
      'condition' => $this->condition,
      'imgUrl' => $this->imgUrl->value(),
      'categories' => $this->categories,
    ];
  }
}
