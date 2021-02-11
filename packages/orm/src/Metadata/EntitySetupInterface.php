<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Metadata;

/**
 * Interface EntitySetupInterface
 */
interface EntitySetupInterface
{
    /**
     * Setup this entity.
     *
     * @param  EntityMetadata  $metadata
     *
     * @return  void
     */
    public static function setup(EntityMetadata $metadata): void;
}
