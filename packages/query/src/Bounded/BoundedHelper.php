<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Bounded;

use Windwalker\Query\Escaper;
use Windwalker\Query\Query;
use Windwalker\Utilities\TypeCast;

/**
 * The QueryHelper class.
 */
class BoundedHelper
{
    public static function replaceParams(string $sql, string $symbol = '?', array $params = []): array
    {
        $values = [];
        $i      = 0;
        $s      = 1;

        $sql = (string) preg_replace_callback(
            '/(:[\w_]+|\?)/',
            static function ($matched) use (
                &$values,
                &$i,
                &$s,
                $symbol,
                $params
            ) {
                $name = $matched[0];

                if ($name === '?') {
                    $values[] = $params[$i];
                    $i++;
                } else {
                    if (!array_key_exists($name, $params) && !array_key_exists(ltrim($name, ':'), $params)) {
                        return $name;
                    }

                    $values[] = $params[$name] ?? $params[ltrim($name, ':')] ?? null;
                }

                if (str_contains($symbol, '%d')) {
                    $symbol = str_replace('%d', (string) $s, $symbol);
                    $s++;
                }

                return $symbol;
            },
            $sql
        );

        return [$sql, $values];
    }

    /**
     * simulatePrepared
     *
     * @param  \PDO|callable|Query|mixed  $escaper
     * @param  string                     $sql
     * @param  array                      $bounded
     *
     * @return  string
     */
    public static function emulatePrepared(mixed $escaper, string $sql, array $bounded): string
    {
        if ($bounded === []) {
            return $sql;
        }

        [$sql, $params] = static::replaceParams($sql, '?', $bounded);

        $values = [];

        foreach ($params as $param) {
            switch ($param['dataType']) {
                case ParamType::STRING:
                    $v = Escaper::tryQuote($escaper, (string) $param['value']);
                    break;
                default:
                    $v = $param['value'];
                    break;
            }

            $values[] = $v;
        }

        if ($values === []) {
            return $sql;
        }

        $sql = str_replace('%', '%%', $sql);
        $sql = str_replace('?', '%s', $sql);

        return sprintf($sql, ...$values);
    }

    public static function forPDO($sql, array $bounded): array
    {
        if ($bounded === []) {
            return [$sql, []];
        }

        [$sql, $params] = static::replaceParams($sql, '?', $bounded);

        return [$sql, array_column($params, 'value')];
    }
}
