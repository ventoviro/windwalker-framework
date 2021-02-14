<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker;

use Windwalker\Attributes\AttributesResolver;
use Windwalker\ORM\Attributes\Table;

if (!function_exists('entity_table')) {
    /**
     * Get Table name from Entity object or class.
     *
     * @param  string|object  $entity
     *
     * @return  string
     */
    function entity_table(string|object $entity): string
    {
        if (!str_contains($entity, '\\') || !class_exists($entity)) {
            return $entity;
        }

        if (is_object($entity)) {
            $entity = $entity::class;
        }

        $tableAttr = AttributesResolver::getFirstAttributeInstance($entity, Table::class);

        if (!$tableAttr) {
            throw new \UnexpectedValueException(
                sprintf(
                    '%s has no table info.',
                    $entity
                )
            );
        }

        return $entity;
    }
}
