<?php

namespace App\Domain\Profile\ValueObjects;

class ProfileImgUrl
{
  private $value;

  public function __construct(string $value)
  {
    $this->validateImgUrl($value);
    $this->value = $value;
  }

  public function validateImgUrl(string $value): void
  {
    // 画像のファイル形式のチェック
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $extension = pathinfo($value, PATHINFO_EXTENSION);
    if (!in_array($extension, $allowedExtensions)) {
      throw new \DomainException('画像のファイル形式が正しくありません。jpg, jpeg, pngのみ対応しています。');
    }

    // ファイルサイズのチェック
    $maxSize = 10 * 1024 * 1024; // 10MB
    if (filesize($value) > $maxSize) {
      throw new \DomainException('画像のファイルサイズが大きすぎます。10MB以下にしてください。');
    }

    // 画像URLの形式が正しいか確認
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
      throw new \DomainException('画像URLの形式が正しくありません');
    }
  }

  public function value(): string
  {
    return $this->value;
  }

  public function isEmpty(): bool
  {
    return empty(trim($this->value));
  }

  /**
   * 値オブジェクトの比較（同値性）
   */
  public function equals(self $other): bool
  {
    return $this->value === $other->value;
  }


  public function __toString(): string
  {
    return $this->value;
  }
}
