<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\Table;

/**
 * The StubSakuraRoseMap class.
 */
#[Table('sakura_rose_maps')]
class StubSakuraRoseMap
{
    #[Column('sakura_no')]
    protected string $sakuraNo = '';

    #[Column('rose_no')]
    protected string $roseNo = '';

    /**
     * @return string
     */
    public function getSakuraNo(): string
    {
        return $this->sakuraNo;
    }

    /**
     * @param  string  $sakuraNo
     *
     * @return  static  Return self to support chaining.
     */
    public function setSakuraNo(string $sakuraNo): static
    {
        $this->sakuraNo = $sakuraNo;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoseNo(): string
    {
        return $this->roseNo;
    }

    /**
     * @param  string  $roseNo
     *
     * @return  static  Return self to support chaining.
     */
    public function setRoseNo(string $roseNo): static
    {
        $this->roseNo = $roseNo;

        return $this;
    }
}
