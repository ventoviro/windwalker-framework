<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Bounded;

use Windwalker\Query\Clause\ValueClause;
use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The ParamType class.
 */
class ParamType
{
    public const STRING = 'string';
    public const INT = 'int';
    public const FLOAT = 'float';
    public const BLOB = 'blob';
    public const BOOL = 'bool';
    public const NULL = 'null';

    private const PDO_MAPS = [
        self::STRING => \PDO::PARAM_STR,
        self::INT => \PDO::PARAM_INT,
        self::FLOAT => \PDO::PARAM_STR,
        self::BLOB => \PDO::PARAM_LOB,
        self::BOOL => \PDO::PARAM_BOOL,
        self::NULL => \PDO::PARAM_NULL,
    ];

    private const MYSQLI_MAPS = [
        self::STRING => 's',
        self::INT => 'i',
        self::FLOAT => 'd',
        self::BLOB => 'b',
        self::BOOL => 'i',
        self::NULL => 'i',
    ];

    /**
     * convertToPDO
     *
     * @param  string|null  $type
     *
     * @return mixed
     */
    public static function convertToPDO(?string $type): mixed
    {
        return static::PDO_MAPS[$type] ?? $type;
    }

    /**
     * convertToMysqli
     *
     * @param  string  $type
     *
     * @return  mixed|string
     */
    public static function convertToMysqli(string $type): mixed
    {
        return static::MYSQLI_MAPS[$type] ?? $type;
    }

    /**
     * guessType
     *
     * @param mixed $value
     *
     * @return  string
     */
    public static function guessType(mixed $value): string
    {
        $dataType = static::STRING;

        if (is_numeric($value)) {
            if (TypeCast::tryInteger($value, true) === null) {
                $dataType = static::INT;
            } else {
                $dataType = static::FLOAT;
            }
        } elseif ($value === null) {
            $dataType = static::NULL;
        }

        return $dataType;
    }
}
