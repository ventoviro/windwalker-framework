<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

/**
 * The CurrentTime class.
 */
#[\Attribute]
class CurrentTime
{
    protected string $time = 'now';

    /**
     * CurrentTime constructor.
     *
     * @param  string  $time
     */
    public function __construct(string $time = 'now')
    {
        $this->time = $time;
    }

    public function getCurrent(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->time);
    }
}
