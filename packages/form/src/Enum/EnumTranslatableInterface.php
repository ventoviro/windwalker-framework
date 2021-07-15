<?php

/**
 * Part of unicorn project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Form\Enum;

use Windwalker\Language\Language;

/**
 * Interface EnumTranslableInterface
 */
interface EnumTranslatableInterface
{
    public function trans(Language $lang, ...$args): string;

    public static function getTransItems(Language $lang, ...$args): array;
}
