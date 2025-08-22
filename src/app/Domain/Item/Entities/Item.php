<?php

namespace App\Domain\Item\Entities;

use App\Domain\Item\ValueObjects\ItemImgUrl;

class Item
{
    private string $id;
    private string $userId;
    private string $name;
    private string $brandName;
    private string $description;
    private int $price;
    private string $condition;
    private ItemImgUrl $imgUrl;
    private bool $isSold;
    private ?array $categories = [];
    private ?array $comments = [];
    private ?array $likes = [];

    public function __construct(
        string $id,
        string $userId,
        string $name,
        string $brandName,
        string $description,
        int $price,
        string $condition,
        ItemImgUrl $imgUrl,
        bool $isSold,
        array $categories,
        array $comments,
        array $likes
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->brandName = $brandName;
        $this->description = $description;
        $this->price = $price;
        $this->condition = $condition;
        $this->imgUrl = $imgUrl;
        $this->isSold = $isSold;
        $this->categories = $categories;
        $this->comments = $comments;
        $this->likes = $likes;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getBrandName(): string
    {
        return $this->brandName;
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

    public function getIsSold(): bool
    {
        return $this->isSold;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function getLikes(): array
    {
        return $this->likes;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setBrandName(string $brandName): void
    {
        $this->brandName = $brandName;
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

    public function setIsSold(bool $isSold): void
    {
        $this->isSold = $isSold;
    }

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }

    public function setLikes(array $likes): void
    {
        $this->likes = $likes;
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
          'brandName' => $this->brandName,
          'description' => $this->description,
          'price' => $this->price,
          'condition' => $this->condition,
          'imgUrl' => $this->imgUrl->value(),
          'isSold' => $this->isSold,
          'categories' => $this->categories,
          'comments' => $this->comments,
          'likes' => $this->likes,
        ];
    }
}
