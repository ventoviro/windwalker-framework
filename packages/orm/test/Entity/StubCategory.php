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
 * The Category class.
 */
#[Table('ww_categories')]
class StubCategory
{
    #[Column('id'), PK, AutoIncrement]
    protected int $id;

    #[Column('title')]
    protected string $title;

    #[Column('ordering')]
    protected int $ordering;

    #[Column('params')]
    #[Cast(JsonCast::class)]
    #[Cast('array')]
    protected array $params;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param  int  $id
     *
     * @return  static  Return self to support chaining.
     */
    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }
}
