<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Steategy;

/**
 * The OneToOne class.
 */
class OneToOne extends AbstractRelationStrategy
{
    /**
     * @inheritDoc
     */
    public function load(array $data): void
    {
        $query = $this->createLoadQuery($data);

        $data[$this->getField()] = fn () => $query->get();
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
