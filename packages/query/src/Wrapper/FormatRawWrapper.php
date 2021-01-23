<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Wrapper;

use Windwalker\Query\Query;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The FormatRawWrapper class.
 */
class FormatRawWrapper extends RawWrapper
{
    /**
     * @var Query
     */
    protected $query;

    /**
     * @var array
     */
    protected $args;

    /**
     * FormatRawWrapper constructor.
     *
     * @param  Query   $query
     * @param  string  $string
     * @param  array   $args
     */
    public function __construct(Query $query, string $string, array $args = [])
    {
        $this->query = $query;
        $this->args = $args;

        parent::__construct($string);
    }

    /**
     * get
     *
     * @return  mixed
     *
     * @since  3.5.1
     */
    public function get(): mixed
    {
        return $this->query->format(parent::get(), ...$this->args);
    }
}
