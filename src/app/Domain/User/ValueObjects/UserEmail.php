<?php

namespace App\Domain\User\ValueObjects;

use App\Models\User;

class UserEmail
{
  private $value;

  public function __construct(string $value)
  {
    $this->validateEmail($value);
    $this->value = $value;
  }

  private function validateEmail(string $value): void
  {
    if (empty(trim($value))) {
      throw new \DomainException('メールアドレスを入力してください');
    }

    if (mb_strlen($value) < 1) {
      throw new \DomainException('メールアドレスは1文字以上で入力してください');
    }

    if (User::where('email', $value)->exists()) {
      throw new \DomainException('このメールアドレスはすでに使用されています');
    }

    // メールアドレスの存在チェック
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
      throw new \DomainException('メールアドレスの形式が正しくありません');
    }
  }

  public function value(): string
  {
    return $this->value;
  }

  public function isEmpty(): bool
  {
    return empty($this->value);
  }

  /**
   * 値オブジェクトの比較（同値性）
   */
  public function equals(self $other): bool
  {
    return $this->value === $other->value;
  }

  /**
   * 文字列としても利用可能
   */
  public function __toString(): string
  {
    return $this->value;
  }
}
