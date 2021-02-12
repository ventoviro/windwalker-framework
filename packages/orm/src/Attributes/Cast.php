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
 * The Cast class.
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_PROPERTY)]
class Cast
{
    public const CONSTRUCTOR = 2;
    public const HYDRATOR = 3;

    protected mixed $cast;

    protected ?int $strategy;

    /**
     * @var mixed
     */
    protected $extract;

    /**
     * Cast constructor.
     *
     * @param  string      $cast
     * @param  mixed|null  $extract
     * @param  int|null    $strategy
     */
    public function __construct(mixed $cast, mixed $extract = null, ?int $strategy = self::CONSTRUCTOR)
    {
        $this->cast     = $cast;
        $this->strategy = $strategy;
        $this->extract  = $extract;
    }

    /**
     * @return mixed
     */
    public function getCast(): mixed
    {
        return $this->cast;
    }

    /**
     * @return mixed
     */
    public function getExtract(): mixed
    {
        return $this->extract;
    }

    /**
     * @return int|null
     */
    public function getStrategy(): ?int
    {
        return $this->strategy;
    }
}
