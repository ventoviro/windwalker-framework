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
 * The RelationStrategyInterface class.
 */
interface RelationStrategyInterface
{
    /**
     * Load all relative children data.
     *
     * @return  void
     */
    public function load(): void;

    /**
     * Store all relative children data.
     *
     * The onUpdate option will work in this method.
     *
     * @return  void
     */
    public function store(): void;

    /**
     * Delete all relative children data.
     *
     * The onDelete option will work in this method.
     *
     * @return  void
     */
    public function delete(): void;
}
