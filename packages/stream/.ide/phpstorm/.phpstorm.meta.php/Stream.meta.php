<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace PHPSTORM_META {

    // Container
    use Windwalker\Stream\Stream;

    registerArgumentsSet(
        'stream_modes',
        \Windwalker\Stream\Stream::MODE_READ_ONLY_FROM_BEGIN,
        \Windwalker\Stream\Stream::MODE_READ_WRITE_FROM_BEGIN,
        \Windwalker\Stream\Stream::MODE_READ_WRITE_FROM_END,
        \Windwalker\Stream\Stream::MODE_READ_WRITE_RESET,
        \Windwalker\Stream\Stream::MODE_WRITE_ONLY_FROM_END,
        \Windwalker\Stream\Stream::MODE_WRITE_ONLY_RESET,
    );

    expectedArguments(
        \Windwalker\Stream\Stream::__construct(),
        1,
        argumentsSet('stream_modes')
    );
}
