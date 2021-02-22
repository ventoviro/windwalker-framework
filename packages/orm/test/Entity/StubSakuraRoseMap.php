<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\DateTimeCast;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;

/**
 * The StubSakuraRoseMap class.
 */
#[Table('sakura_rose_maps')]
class StubSakuraRoseMap implements EntityInterface
{
    use EntityTrait;

    #[Column('sakura_no')]
    protected string $sakuraNo = '';

    #[Column('rose_no')]
    protected string $roseNo = '';

    #[Column('type')]
    protected string $type = '';

    #[Column('created')]
    #[Cast(DateTimeCast::class)]
    protected ?\DateTimeImmutable $created = null;

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

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param  string  $type
     *
     * @return  static  Return self to support chaining.
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @param  \DateTimeImmutable  $created
     *
     * @return  static  Return self to support chaining.
     */
    public function setCreated(\DateTimeImmutable $created): static
    {
        $this->created = $created;

        return $this;
    }
}
