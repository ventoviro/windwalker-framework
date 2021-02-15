<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;

/**
 * The StubRose class.
 */
#[Table('roses')]
class StubRose
{
    #[PK, AutoIncrement]
    #[Column('id')]
    protected ?int $id = null;

    #[Column('location')]
    protected int $location = 0;

    #[Column('sakura_id')]
    protected int $sakuraId = 0;

    #[Column('title')]
    protected string $title = '';

    #[Column('state')]
    protected int $state = 0;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param  int|null  $id
     *
     * @return  static  Return self to support chaining.
     */
    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocation(): int
    {
        return $this->location;
    }

    /**
     * @param  int  $location
     *
     * @return  static  Return self to support chaining.
     */
    public function setLocation(int $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return int
     */
    public function getSakuraId(): int
    {
        return $this->sakuraId;
    }

    /**
     * @param  int  $sakuraId
     *
     * @return  static  Return self to support chaining.
     */
    public function setSakuraId(int $sakuraId): static
    {
        $this->sakuraId = $sakuraId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param  string  $title
     *
     * @return  static  Return self to support chaining.
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param  int  $state
     *
     * @return  static  Return self to support chaining.
     */
    public function setState(int $state): static
    {
        $this->state = $state;

        return $this;
    }
}
