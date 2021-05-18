<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Response;

/**
 * The HtmlResponse class.
 *
 * @since  3.0
 */
class HtmlResponse extends TextResponse
{
    /**
     * Content type.
     *
     * @var  string
     */
    protected string $type = 'text/html';
}
