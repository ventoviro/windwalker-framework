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
        \Windwalker\ORM\ORM::hydrateEntity(0),
        type(0)
    );

    // Types
    override(
        \Windwalker\ORM\Strategy\Selector::get(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        \Windwalker\ORM\Strategy\Selector::all(0),
        map(
            [
                '' => '@',
            ]
        )
    );
}
