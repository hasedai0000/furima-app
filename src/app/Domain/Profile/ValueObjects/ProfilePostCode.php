<?php

namespace App\Domain\Profile\ValueObjects;

class ProfilePostCode
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validatePostCode($value);
        $this->value = $value;
    }

    public function validatePostCode(string $value): void
    {
        // ハイフンを除去して数字のみの文字列を作成
        $numericValue = str_replace('-', '', $value);

        // 郵便番号の桁数が正しいか確認（7桁）
        if (strlen($numericValue) !== 7) {
            throw new \DomainException('郵便番号の桁数が正しくありません。');
        }

        // 郵便番号が数字のみか確認
        if (! ctype_digit($numericValue)) {
            throw new \DomainException('郵便番号は数字で入力してください。');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * ハイフン付きの郵便番号を返す
     *
     * @return string
     */
    public function formattedValue(): string
    {
        // ハイフンを除去して数字のみの文字列を作成
        $numericValue = str_replace('-', '', $this->value);

        // 7桁の数字をXXX-XXXX形式にフォーマット
        return substr($numericValue, 0, 3) . '-' . substr($numericValue, 3);
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
