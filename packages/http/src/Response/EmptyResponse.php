<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Response;

use Windwalker\Stream\NullStream;

/**
 * The EmptyResponse class.
 *
 * Always return empty data and is only readable. THe headers will still send.
 *
 * @since  3.0
 */
class EmptyResponse extends Response
{
    /**
     * Constructor.
     *
     * @param  int    $status   The status code.
     * @param  array  $headers  The custom headers.
     */
    public function __construct(int $status = 204, array $headers = [])
    {
        parent::__construct(new NullStream(), $status, $headers);
    }
}
