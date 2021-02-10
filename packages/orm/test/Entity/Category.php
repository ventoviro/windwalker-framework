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
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\JsonCast;

/**
 * The Category class.
 */
#[Table('ww_categories')]
class Category
{
    #[PK]
    #[Column('id')]
    protected int $id;

    #[Column('title')]
    protected string $title;

    #[Column('ordering')]
    protected int $ordering;

    #[Column('params')]
    #[Cast(JsonCast::class)]
    protected array $params;
}
