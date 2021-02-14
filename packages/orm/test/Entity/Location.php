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

/**
 * The Location class.
 */
#[Table('locations')]
class Location extends AbstractEntity
{
    #[PK, AutoIncrement]
    #[Column('id')]
    protected ?int $id = null;

    #[Column('title')]
    protected string $title = '';

    #[Column('state')]
    protected int $state = 0;

    protected ?LocationData $data = null;

    #[EntitySetup]
    public static function setup(EntityMetadata $metadata): void
    {
        $rm = $metadata->getRelationManager();

        $rm->oneToOne('data')
            ->target(LocationData::class, ['id' => 'location_id']);
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
     * @return LocationData|null
     */
    public function getData(): ?LocationData
    {
        return $this->data ??= $this->loadRelation('data');
    }

    /**
     * @param  LocationData  $data
     *
     * @return  static  Return self to support chaining.
     */
    public function setData(LocationData $data): static
    {
        $this->data = $data;

        return $this;
    }
}
