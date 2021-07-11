<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace PHPSTORM_META {

    override(
        \Windwalker\DI\ServiceAwareTrait::make(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        \Windwalker\DI\ServiceAwareTrait::service(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        \Windwalker\DI\ServiceAwareTrait::resolve(0),
        map(
            [
                '' => '@',
            ]
        )
    );
}
