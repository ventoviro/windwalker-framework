<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Query\Clause;

/**
 * The ExprClause class.
 */
class ExprClause extends Clause
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name = '', ...$elements)
    {
        parent::__construct($name, $elements, ', ');
    }
}
