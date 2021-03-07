<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

use Opis\Closure\Library;

include_once __DIR__ . '/functions.php';

if (
    ini_get('ffi.enable')
    && class_exists(Library::class)
    && class_exists(\FFI::class)
) {
    Library::init();
}
