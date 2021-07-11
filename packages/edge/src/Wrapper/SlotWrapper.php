<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Wrapper;

use Closure;
use Stringable;

/**
 * The SlotWrapper class.
 */
class SlotWrapper implements Stringable
{
    /**
     * SlotWrapper constructor.
     *
     * @param  Closure  $slotCallback
     */
    public function __construct(protected Closure $slotCallback)
    {
    }

    public function __invoke(...$args): string
    {
        ob_start();

        ($this->slotCallback)(...$args);

        return ob_get_clean();
    }

    /**
     * Magic method {@see https://www.php.net/manual/en/language.oop5.magic.php}
     * called during serialization to string.
     *
     * @return string Returns string representation of the object that
     * implements this interface (and/or "__toString" magic method).
     */
    public function __toString(): string
    {
        return $this();
    }
}
