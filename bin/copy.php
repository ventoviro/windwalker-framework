<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

$packages = glob(__DIR__ . '/../packages/*');
print_r($packages);
foreach ($packages as $package) {
    $file = $package . '/phpunit.travis.xml';

    file_put_contents(
        $package . '/phpunit.ci.xml',
        file_get_contents($file)
    );
}
