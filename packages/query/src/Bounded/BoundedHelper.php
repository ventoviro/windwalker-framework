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
    /**
     * Replace all named params to ordered params.
     *
     * @param  string  $sql
     * @param  string  $sign
     * @param  array   $params
     *
     * @return  array
     */
    public static function replaceParams(string $sql, string $sign = '?', array $params = []): array
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
                $sign,
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

                if (str_contains($sign, '%d')) {
                    $sign = str_replace('%d', (string) $s, $sign);
                    $s++;
                }

                return $sign;
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
            $v = match ($param['dataType']) {
                ParamType::STRING => Escaper::tryQuote($escaper, (string) $param['value']),
                default => $param['value'],
            };

            $values[] = $v;
        }

        if ($values === []) {
            return $sql;
        }

        $sql = str_replace(['%', '?'], ['%%', '%s'], $sql);

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
