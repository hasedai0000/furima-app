<?php

namespace App\Domain\Profile\ValueObjects;

class ProfilePostCode
{
  private $value;

  public function __construct(string $value)
  {
    $this->validatePostCode($value);
    $this->value = $value;
  }

  public function validatePostCode(string $value): void
  {
    // 郵便番号の桁数が正しいか確認
    if (strlen($value) !== 8) {
      throw new \DomainException('郵便番号の桁数が正しくありません。');
    }

    // 郵便番号が数字のみか確認
    if (!ctype_digit($value)) {
      throw new \DomainException('郵便番号は数字で入力してください。');
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
