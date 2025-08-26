<?php

namespace App\Domain\Purchase\ValueObjects;

class PaymentMethod
{
    // 支払い方法の定数
    public const CREDIT_CARD = 'credit_card';
    public const CONVENIENCE_STORE = 'convenience_store';

    // 支払い方法の表示名
    public const LABELS = [
        self::CREDIT_CARD => 'クレジットカード',
        self::CONVENIENCE_STORE => 'コンビニ払い',
    ];

    private string $value;

    public function __construct(string $value)
    {
        $this->validatePaymentMethod($value);
        $this->value = $value;
    }

    private function validatePaymentMethod(string $value): void
    {
        if (! in_array($value, [
            self::CREDIT_CARD,
            self::CONVENIENCE_STORE,
        ], true)) {
            throw new \DomainException('無効な支払い方法です');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return self::LABELS[$this->value] ?? '';
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

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * 全ての支払い方法のオプションを取得
     */
    public static function getOptions(): array
    {
        return self::LABELS;
    }
}
