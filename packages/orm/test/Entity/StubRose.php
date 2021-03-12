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
use Windwalker\ORM\Attributes\Mapping;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Relation\RelationCollection;

/**
 * The StubRose class.
 */
#[Table('roses', 'rose')]
class StubRose implements EntityInterface
{
    use EntityTrait;

    #[Column('id'), PK, AutoIncrement]
    protected ?int $id = null;

    #[Column('no')]
    protected string $no = '';

    #[Column('location_no')]
    protected string $locationNo = '';

    #[Column('sakura_no')]
    protected string $sakuraNo = '';

    #[Column('title')]
    protected string $title = '';

    #[Column('state')]
    protected int $state = 0;

    protected ?RelationCollection $sakuras = null;

    #[Mapping('sakura_rose_map')]
    #[Cast(StubSakuraRoseMap::class, strategy: Cast::HYDRATOR)]
    protected ?StubSakuraRoseMap $map = null;

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
     * @return string
     */
    public function getLocationNo(): string
    {
        return $this->locationNo;
    }

    /**
     * @param  string  $locationNo
     *
     * @return  static  Return self to support chaining.
     */
    public function setLocationNo(string $locationNo): static
    {
        $this->locationNo = $locationNo;

        return $this;
    }

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

    /**
     * @return string
     */
    public function getNo(): string
    {
        return $this->no;
    }

    /**
     * @param  string  $no
     *
     * @return  static  Return self to support chaining.
     */
    public function setNo(string $no): static
    {
        $this->no = $no;

        return $this;
    }

    /**
     * @return RelationCollection|null
     */
    public function getSakuras(): RelationCollection
    {
        return $this->loadCollection('sakuras');
    }

    /**
     * @param  RelationCollection|null  $sakuras
     *
     * @return  static  Return self to support chaining.
     */
    public function setSakuras(?RelationCollection $sakuras): static
    {
        $this->sakuras = $sakuras;

        return $this;
    }

    /**
     * @return StubSakuraRoseMap|null
     */
    public function getMap(): ?StubSakuraRoseMap
    {
        return $this->map;
    }

    /**
     * @param  StubSakuraRoseMap|null  $map
     *
     * @return  static  Return self to support chaining.
     */
    public function setMap(?StubSakuraRoseMap $map): static
    {
        $this->map = $map;

        return $this;
    }
}
