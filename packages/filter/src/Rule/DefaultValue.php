<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractFilter;

/**
 * The DefaultValue class.
 */
class DefaultValue extends AbstractFilter
{
    /**
     * @var mixed
     */
    protected $default;

    /**
     * DefaultValue constructor.
     *
     * @param  mixed  $default
     */
    public function __construct(mixed $default)
    {
        $this->default = $default;
    }

    /**
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        return $value === '' || $value === null ? $this->default : $value;
    }
}
