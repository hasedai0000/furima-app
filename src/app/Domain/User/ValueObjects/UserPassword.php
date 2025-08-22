<?php

namespace App\Domain\User\ValueObjects;

class UserPassword
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validatePassword($value);
        $this->value = $value;
    }

    private function validatePassword(string $value): void
    {
        if (empty(trim($value))) {
            throw new \DomainException('パスワードを入力してください');
        }

        if (mb_strlen($value) < 8) {
            throw new \DomainException('パスワードは8文字以上で入力してください');
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
