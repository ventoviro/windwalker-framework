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
     * @param  array   $data
     * @param  object  $entity
     *
     * @return array
     */
    public function load(array $data, object $entity): array;

    /**
     * Store all relative children data.
     *
     * The onUpdate option will work in this method.
     *
     * @param  array   $data
     * @param  object  $entity
     *
     * @return  void
     */
    public function save(array $data, object $entity): void;

    /**
     * Delete all relative children data.
     *
     * The onDelete option will work in this method.
     *
     * @param  array   $data
     * @param  object  $entity
     *
     * @return  void
     */
    public function delete(array $data, object $entity): void;
}
