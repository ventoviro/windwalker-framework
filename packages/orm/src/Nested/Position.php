<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Nested;

/**
 * The Position class.
 */
class Position
{
    public const MOVE_UP = -1;

    public const MOVE_DOWN = 1;

    public const BEFORE = 2;

    public const AFTER = 4;

    public const FIRST_CHILD = 6;

    public const LAST_CHILD = 8;

    public const POSITIONS = [
        self::BEFORE,
        self::AFTER,
        self::FIRST_CHILD,
        self::LAST_CHILD,
    ];

    /**
     * Position constructor.
     *
     * @param  mixed  $referenceId
     * @param  int    $position
     */
    public function __construct(
        protected mixed $referenceId = null,
        protected int $position = self::LAST_CHILD
    ) {
        //
    }

    /**
     * @return mixed
     */
    public function getReferenceId(): mixed
    {
        return $this->referenceId;
    }

    /**
     * @param  mixed  $referenceId
     *
     * @return  static  Return self to support chaining.
     */
    public function setReferenceId(mixed $referenceId): static
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param  int  $position
     *
     * @return  static  Return self to support chaining.
     */
    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
