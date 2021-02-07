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
use Windwalker\ORM\TableAwareInterface;

/**
 * The User class.
 */
class User implements TableAwareInterface
{
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
