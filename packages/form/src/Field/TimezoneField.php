<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field;

use DateTimeZone;

/**
 * The TimezoneField class.
 *
 * @since  2.0
 */
class TimezoneField extends ListField
{
    /**
     * prepareOptions
     *
     * @return  array
     */
    protected function prepareOptions(): array
    {
        $zones = [];

        foreach (DateTimeZone::listIdentifiers() as $zone) {
            $pos = explode('/', $zone);

            if (count($pos) === 2) {
                $state = $pos[0];
                $city  = $pos[1];
            } else {
                $state = $pos[0];
                $city  = $pos[0];
            }

            if (!isset($zones[$state])) {
                $zones[$state] = [];
            }

            $zones[$state][] = static::createOption($city, $zone);
        }

        return $zones;
    }
}
