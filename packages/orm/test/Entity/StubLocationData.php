<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;

/**
 * The LocationData class.
 */
#[Table('location_data')]
class StubLocationData  implements EntityInterface
{
    use EntityTrait;
    #[PK, AutoIncrement]
    #[Column('id')]
    protected ?int $id = null;

    #[Column('location_no')]
    protected string $locationNo = '';

    #[Column('data')]
    protected string|\stdClass $data = '';

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param  string  $data
     *
     * @return  static  Return self to support chaining.
     */
    public function setData(string $data): static
    {
        $this->data = $data;

        return $this;
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
}
