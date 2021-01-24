<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Exception;

use Exception;

/**
 * The EdgeException class.
 *
 * @since  3.1.3
 */
class EdgeException extends Exception
{
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @link  http://php.net/manual/en/exception.construct.php
     *
     * @param  null  $message   The Exception message to throw.
     * @param  null  $code      The Exception code.
     * @param  null  $file      File name.
     * @param  null  $line      File line.
     * @param  null  $previous  The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = null, $file = null, $line = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if ($file) {
            $this->file = $file;
        }

        if ($line) {
            $this->line = $line;
        }
    }
}
