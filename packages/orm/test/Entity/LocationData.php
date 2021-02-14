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
 * The LocationData class.
 */
#[Table('location_data')]
class LocationData
{
    #[Column('id')]
    protected ?int $id = null;

    #[Column('location_id')]
    protected int $locationId = 0;

    #[Column('data')]
    protected string $data = '';

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
}
