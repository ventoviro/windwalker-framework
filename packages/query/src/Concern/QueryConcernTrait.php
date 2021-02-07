<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Concern;

use Windwalker\Query\Query;
use Windwalker\Utilities\Str;
use Windwalker\Utilities\TypeCast;

/**
 * Trait QueryHelperTrait
 */
trait QueryConcernTrait
{
    /**
     * convertArrayToWheres
     *
     * @param  Query  $query
     * @param  mixed  $wheres
     *
     * @return  Query
     */
    public static function convertAllToWheres(Query $query, mixed $wheres): Query
    {
        if ($wheres === null) {
            return $query;
        }

        if (is_callable($wheres)) {
            return $query->where($wheres);
        }

        $wheres = TypeCast::toArray($wheres);

        foreach ($wheres as $key => $where) {
            if (!is_numeric($key)) {
                $query->where($key, '=', $where);
                continue;
            }

            if (is_string($where)) {
                $query->whereRaw($where);
                continue;
            }

            if (is_array($wheres)) {
                return $query->where(...$wheres);
            }

            $query->where($where);
        }

        return $query;
    }

    public function formatDateTime(\DateTimeInterface $dateTime): string
    {
        return $dateTime->format($this->getDateFormat());
    }

    public function stripQuote(string $str): string
    {
        return $this->getEscaper()::stripQuoteIfExists($str);
    }

    public function stripNameQuote(string $str): string
    {
        $grammar = $this->getGrammar();
        $nq = $grammar::$nameQuote;

        return $this->getEscaper()::stripQuoteIfExists($str, $nq[0], $nq[1]);
    }
}
