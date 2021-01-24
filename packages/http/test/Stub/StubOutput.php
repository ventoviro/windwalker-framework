<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Stub;

use Windwalker\Http\Output\Output;
use Windwalker\Http\Response\Response;

/**
 * The MockOutput class.
 *
 * @since  3.0
 */
class StubOutput extends Output
{
    /**
     * Property message.
     *
     * @var  Response
     */
    public $message;

    /**
     * Property status.
     *
     * @var  int
     */
    public $status;

    /**
     * Property others.
     *
     * @var  array
     */
    public $others = [];

    /**
     * MockOutput constructor.
     */
    public function __construct()
    {
        $this->message = new Response();
    }

    /**
     * header
     *
     * @param  string  $string
     * @param  bool    $replace
     * @param  int     $code
     *
     * @return  static
     */
    public function header($string, $replace = true, $code = null): static
    {
        if (strpos($string, ':') !== false) {
            [$header, $value] = explode(': ', $string, 2);

            if ($replace) {
                $this->message = $this->message->withHeader($header, $value);
            } else {
                $this->message = $this->message->withAddedHeader($header, $value);
            }
        } elseif (strpos($string, 'HTTP') === 0) {
            $this->status = $string;
        } else {
            $this->others[] = $string;
        }

        return $this;
    }
}
