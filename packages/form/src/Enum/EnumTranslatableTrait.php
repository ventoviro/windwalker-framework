<?php

/**
 * Part of unicorn project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Form\Enum;

use MyCLabs\Enum\Enum;
use Windwalker\Language\Language;

/**
 * Trait EnumTranslableTrait
 */
trait EnumTranslatableTrait
{
    public static function getTransItems(Language $lang, ...$args): array
    {
        $items = [];

        /** @var Enum $item */
        foreach (static::values() as $item) {
            $items[$item->getValue()] = $item->trans($lang, ...$args);
        }

        return $items;
    }
}
