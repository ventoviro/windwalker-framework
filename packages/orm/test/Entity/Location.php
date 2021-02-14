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
use Windwalker\ORM\Attributes\EntitySetup;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The Location class.
 */
#[Table('locations')]
class Location
{
    #[Column('id')]
    protected ?int $id = null;

    #[Column('title')]
    protected string $title = '';

    #[Column('state')]
    protected int $state = 0;

    protected LocationData $data;

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
}
