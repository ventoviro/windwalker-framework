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
        \Windwalker\Database\Manager\WriterManager::insertOne(1),
        type(1)
    );

    override(
        \Windwalker\Database\Manager\WriterManager::updateOne(1),
        type(1)
    );

    override(
        \Windwalker\Database\Manager\WriterManager::saveOne(1),
        type(1)
    );

    override(
        \Windwalker\Database\Manager\WriterManager::insertMultiple(1),
        type(1)
    );

    override(
        \Windwalker\Database\Manager\WriterManager::updateMultiple(1),
        type(1)
    );

    override(
        \Windwalker\Database\Manager\WriterManager::saveMultiple(1),
        type(1)
    );
}
