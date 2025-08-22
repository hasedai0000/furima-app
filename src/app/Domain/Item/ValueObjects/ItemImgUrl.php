<?php

namespace App\Domain\Item\ValueObjects;

class ItemImgUrl
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validateImgUrl($value);
        $this->value = $value;
    }

    public function validateImgUrl(string $value): void
    {
        // 空文字列の場合は許可（nullの代わり）
        if (empty(trim($value))) {
            return;
        }

        // URLの場合
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return;
        }

        // ファイルパスの場合（storage/のような形式）
        if (strpos($value, 'storage/') === 0) {
            return;
        }

        // その他の場合はエラー
        throw new \DomainException('画像URLの形式が正しくありません');
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
