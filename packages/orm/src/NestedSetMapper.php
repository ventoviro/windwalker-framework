<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM;

use Windwalker\Data\Collection;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The NestedSetMapper class.
 */
class NestedSetMapper extends EntityMapper
{
    /**
     * getAncestors
     *
     * @param  mixed  $pk
     *
     * @return  Collection
     */
    public function getAncestors(mixed $pk): Collection
    {
        $metadata = $this->getMetadata();
        $key = $metadata->getMainKey();

        return $this->getORM()
            ->select()
            ->from(
                [
                    [$metadata->getClassName(), 'n'],
                    [$metadata->getClassName(), 'p']
                ]
            )
            ->where('n.lft', 'between', ['p.lft', 'p.rgt'])
            ->where('n.' . $key, '=', $pk)
            ->order('p.lft')
            ->all();
    }
}
