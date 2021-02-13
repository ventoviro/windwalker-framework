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
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\JsonCast;

/**
 * The Flower class.
 */
#[Table('ww_flower')]
class Flower
{
    #[PK, AutoIncrement]
    #[Column('id')]
    public ?int $id;

    #[Column('catid')]
    protected int $catid = 0;

    #[Column('title')]
    protected string $title = '';

    #[Column('meaning')]
    protected string $meaning = '';

    #[Column('ordering')]
    protected int $ordering = 0;

    #[Column('state')]
    public int $state = 0;

    #[Column('params')]
    #[
        Cast(JsonCast::class),
        Cast('array')
    ]
    public array $params = [];

    public array $c = [];

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
    public function getCatid(): int
    {
        return $this->catid;
    }

    /**
     * @param  int  $catid
     *
     * @return  static  Return self to support chaining.
     */
    public function setCatid(int $catid): static
    {
        $this->catid = $catid;

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
     * @return string
     */
    public function getMeaning(): string
    {
        return $this->meaning;
    }

    /**
     * @param  string  $meaning
     *
     * @return  static  Return self to support chaining.
     */
    public function setMeaning(string $meaning): static
    {
        $this->meaning = $meaning;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrdering(): int
    {
        return $this->ordering;
    }

    /**
     * @param  int  $ordering
     *
     * @return  static  Return self to support chaining.
     */
    public function setOrdering(int $ordering): static
    {
        $this->ordering = $ordering;

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

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param  array  $params
     *
     * @return  static  Return self to support chaining.
     */
    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }
}
