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
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;

/**
 * The Flower class.
 */
#[Table('ww_flower')]
class Flower
{
    #[PK]
    #[Column('id')]
    protected int $id;

    #[Column('catid')]
    protected int $catid;

    #[Column('title')]
    protected int $title;

    #[Column('meaning')]
    protected string $meaning;

    #[Column('ordering')]
    protected int $ordering;

    #[Column('state')]
    protected int $state;

    #[Column('params')]
    protected array $params;

    protected array $c;
}
