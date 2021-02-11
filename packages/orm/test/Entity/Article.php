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
 * The Article class.
 */
#[Table('articles')]
class Article
{
    #[PK]
    #[Column('id')]
    protected int $id;

    #[Column('title')]
    protected string $title;

    #[Column('image')]
    protected string $image;

    #[Column('content')]
    protected string $content;

    #[Column('state')]
    protected string $state;

    #[Column('created')]
    #[Cast(\DateTimeImmutable::class)]
    protected \DateTimeImmutable $created;

    #[Column('created_by')]
    protected int $createdBy;

    #[Column('params')]
    #[Cast(JsonCast::class)]
    #[Cast('array')]
    protected ?array $params;
}
