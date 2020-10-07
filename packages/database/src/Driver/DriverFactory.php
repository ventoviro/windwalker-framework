<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\Pdo\PdoDriver;
use Windwalker\Database\Platform\AbstractPlatform;

/**
 * The DriverFactory class.
 */
class DriverFactory
{
    public static function create(string $name, DatabaseAdapter $db): AbstractDriver
    {
        $names = explode('_', $name);

        $platformName = ucfirst(AbstractPlatform::getShortName($names[0]));

        $driverClass = sprintf(
            __NAMESPACE__ . '\%s\%sDriver',
            $platformName,
            $platformName
        );

        $driver = new $driverClass($db);

        if (($driver instanceof PdoDriver) && isset($names[1])) {
            $driver->setPlatformName($names[1]);
        }

        return $driver;
    }

    public static function getPlatformName($name): string
    {
        $names = explode('_', $name, 2);

        return $names[1] ?? $names[0];
    }
}
