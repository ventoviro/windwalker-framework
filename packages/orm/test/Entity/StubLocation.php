<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use Windwalker\ORM\AbstractEntity;
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\EntitySetup;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Relation\RelationCollection;
use Windwalker\ORM\Strategy\Selector;

/**
 * The Location class.
 */
#[Table('locations')]
class StubLocation extends AbstractEntity
{
    #[PK, AutoIncrement]
    #[Column('id')]
    protected ?int $id = null;

    #[Column('no')]
    protected string $no = '';

    #[Column('title')]
    protected string $title = '';

    #[Column('state')]
    protected int $state = 0;

    protected ?StubLocationData $data = null;

    protected RelationCollection|null $sakuras = null;

    protected RelationCollection|null $roses = null;

    #[EntitySetup]
    public static function setup(EntityMetadata $metadata): void
    {
        $rm = $metadata->getRelationManager();

        $rm->oneToOne('data')
            ->target(StubLocationData::class, ['no' => 'location_no']);
    }

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
     * @return StubLocationData|null
     */
    public function getData(): ?StubLocationData
    {
        return $this->data ??= $this->loadChild('data');
    }

    /**
     * @param  StubLocationData  $data
     *
     * @return  static  Return self to support chaining.
     */
    public function setData(StubLocationData $data): static
    {
        $this->data = $data;

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
     * @return RelationCollection|null
     */
    public function getSakuras(): ?RelationCollection
    {
        return $this->sakuras ??= $this->loadCollection('sakuras');
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
     * @return RelationCollection|null
     */
    public function getRoses(): ?RelationCollection
    {
        return $this->roses ??= $this->loadCollection('roses');
    }

    /**
     * @param  RelationCollection|null  $roses
     *
     * @return  static  Return self to support chaining.
     */
    public function setRoses(?RelationCollection $roses): static
    {
        $this->roses = $roses;

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
}
