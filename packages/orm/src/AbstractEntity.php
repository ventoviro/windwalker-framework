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
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\Utilities\Contract\DumpableInterface;
use Windwalker\Utilities\TypeCast;

/**
 * The AbstractEntity class.
 */
class AbstractEntity implements \JsonSerializable, DumpableInterface
{
    protected function loadRelation(string $propName): mixed
    {
        return RelationProxies::call($this, $propName);
    }

    public function loadAllRelations(): void
    {
        foreach ($this->dump() as $prop => $value) {
            if ($value === null && RelationProxies::has($this, $prop)) {
                $this->$prop = $this->loadRelation($prop);
            }
        }
    }

    public function toCollection(): Collection
    {
        return Collection::wrap($this->dump());
    }

    /**
     * Dump to array. but keep properties types.
     *
     * @inheritDoc
     */
    public function dump(bool $recursive = false, bool $onlyDumpable = false): array
    {
        return TypeCast::toArray(get_object_vars($this), $recursive, $onlyDumpable);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $this->loadAllRelations();

        return $this->dump();
    }
}
