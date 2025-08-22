<?php

namespace App\Domain\Item\ValueObjects;

class ItemCondition
{
    // 商品の状態の定数
    public const GOOD = 'good';
    public const NO_DAMAGE = 'no_damage';
    public const SOME_DAMAGE = 'some_damage';
    public const BAD = 'bad';

    // 商品の状態の表示名
    public const LABELS = [
     self::GOOD => '良好',
     self::NO_DAMAGE => '目立った傷や汚れなし',
     self::SOME_DAMAGE => 'やや傷や汚れあり',
     self::BAD => '状態が悪い',
    ];

    private string $value;

    public function __construct(string $value)
    {
        $this->validateItemCondition($value);
        $this->value = $value;
    }

    private function validateItemCondition(string $value): void
    {
        if (! in_array($value, [
         self::GOOD,
         self::NO_DAMAGE,
         self::SOME_DAMAGE,
         self::BAD,
        ], true)) {
            throw new \DomainException('無効な商品の状態です');
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
     * 全ての商品の状態のオプションを取得
     */
    public static function getOptions(): array
    {
        return self::LABELS;
    }
}
