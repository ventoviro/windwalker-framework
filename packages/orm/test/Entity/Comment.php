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

/**
 * The Comment class.
 */
#[Table('comments')]
class Comment
{
    #[Column('id')]
    #[PK(true)]
    protected int $id;

    #[Column('target_id')]
    #[PK]
    protected int $targetId;

    #[Column('user_id')]
    #[PK]
    protected int $userId;

    #[Column('type')]
    #[PK]
    protected string $type;

    #[Column('content')]
    protected string $content;

    #[Column('created')]
    #[Cast(\DateTimeImmutable::class)]
    protected \DateTimeImmutable $created;

    #[Column('created_by')]
    protected int $createdBy;
}
