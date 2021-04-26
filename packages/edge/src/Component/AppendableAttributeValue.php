<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Component;

class AppendableAttributeValue
{
    /**
     * The attribute value.
     *
     * @var mixed
     */
    public mixed $value;

    /**
     * Create a new appendable attribute value.
     *
     * @param  mixed  $value
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * Get the string value.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}
