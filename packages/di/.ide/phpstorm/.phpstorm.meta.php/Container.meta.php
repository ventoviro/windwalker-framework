<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace PHPSTORM_META {

    use Windwalker\DI\Container;

    override(
        Container::get(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        Container::createObject(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        Container::createSharedObject(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        Container::newInstance(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        Container::resolve(0),
        map(
            [
                '' => '@',
            ]
        )
    );
}
