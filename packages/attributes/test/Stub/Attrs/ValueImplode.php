<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Attributes\Test\Stub\Attrs;

use Attribute;
use Closure;
use Windwalker\Attributes\AttributeHandler;

/**
 * The ValueImplode class.
 */
#[Attribute]
class ValueImplode
{
    protected string $sep = '';

    /**
     * ValueImplode constructor.
     *
     * @param  string  $sep
     */
    public function __construct(string $sep = '')
    {
        $this->sep = $sep;
    }

    public function __invoke(AttributeHandler $handler): Closure
    {
        return function (...$args) use ($handler) {
            $value = $handler(...$args);

            if (!is_array($value)) {
                return $value;
            }

            return implode($this->sep, $value);
        };
    }
}
