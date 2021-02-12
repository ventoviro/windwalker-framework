<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace PHPSTORM_META {

    // Types

    override(
        \Windwalker\Query\Query::get(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        \Windwalker\Query\Query::all(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    // Options
    registerArgumentsSet(
        'query_join_types',
        'INNER',
        'OUTER',
        'LEFT',
        'RIGHT'
    );

    expectedArguments(
        \Windwalker\Query\Query::join(),
        0,
        argumentsSet('query_join_types')
    );

    registerArgumentsSet(
        'query_union_types',
        '',
        'DISTINCT',
        'ALL'
    );

    expectedArguments(
        \Windwalker\Query\Query::union(),
        1,
        argumentsSet('query_union_types')
    );

    // todo: add row lock hints
}
