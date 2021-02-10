<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use Windwalker\Data\Collection;
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\JsonCast;
use Windwalker\ORM\TableAwareInterface;

/**
 * The User class.
 */
#[Table('users')]
class User implements TableAwareInterface
{
    #[Column('id')]
    #[PK]
    #[AutoIncrement]
    protected int $id;

    #[Column('name')]
    protected string $name;

    #[Column('email')]
    protected string $email;

    #[Column('password')]
    protected string $password;

    #[Column('avatar')]
    protected string $avatar;

    #[Column('registered')]
    #[Cast(\DateTimeImmutable::class)]
    protected \DateTimeImmutable $registered;

    #[Cast(JsonCast::class)]
    protected array $params = [];



    /**
     * @inheritDoc
     */
    public static function table(): string
    {
        return 'users';
    }

    /**
     * getArray
     *
     * @return  array<int, Collection>
     */
    public static function getArray(): array
    {

    }

    public function hello(): string
    {
        return '';
    }
}
