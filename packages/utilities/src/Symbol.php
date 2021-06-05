<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities;

/**
 * The Symbol class.
 *
 * @method static self main()
 * @method static self root()
 * @method static self empty()
 * @method static self full()
 * @method static self none()
 * @method static self all()
 * @method static self noAction()
 */
class Symbol
{
    protected static array $instances = [];

    /**
     * Symbol constructor.
     *
     * @param  string  $value
     */
    public function __construct(protected string $value)
    {
    }

    private static function wrapValue(string $value): string
    {
        return '__' . strtoupper(StrNormalize::toUnderscoreSeparated($value)) . '__';
    }

    public static function create(string $value): static
    {
        return static::$instances[$value] ??= new static($value);
    }

    public function is(mixed $value): bool
    {
        if ($value instanceof static) {
            $value = $value->getValue();
        }

        return $this->value === $value;
    }

    public static function same(self $symbol1, self $symbol2): bool
    {
        return $symbol1 === $symbol2;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public static function __callStatic(string $name, array $args): static
    {
        return static::create(static::wrapValue($name));
    }
}
