<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation;

use Windwalker\ORM\Relation\Steategy\RelationStrategyInterface;

/**
 * The Relation class.
 */
class Relation implements RelationStrategyInterface
{
    /**
     * @inheritDoc
     */
    public function load(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function store(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function delete(): void
    {
    }
}
